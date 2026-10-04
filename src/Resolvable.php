<?php
declare(strict_types=1);

namespace Raxos\Router;

use BackedEnum;
use JsonSerializable;
use Raxos\Collection\Map;
use Raxos\Contract\Router\RouterInterface;
use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Foundation\Contract\StringParsableInterface;
use Raxos\Http\HttpMethod;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\Response\MethodNotAllowedHttpResponse;
use Raxos\Http\Response\NotFoundHttpResponse;
use Raxos\Router\Error\InvalidHandlerException;
use Raxos\Router\Error\InvalidRouteParametersException;
use Raxos\Router\Frame\ControllerFrame;
use Raxos\Router\Frame\FrameStack;
use Raxos\Router\Frame\RouteFrame;
use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_key_first;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function class_exists;
use function count;
use function http_build_query;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function method_exists;
use function preg_match;
use function preg_replace_callback;
use function rawurldecode;
use function rawurlencode;
use function str_starts_with;
use function strtoupper;
use const ARRAY_FILTER_USE_BOTH;
use const PHP_QUERY_RFC3986;

/**
 * Trait Resolvable
 *
 * Shares request resolution and reverse-route URL construction between router implementations.
 *
 * @implements RouterInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.5.0
 */
trait Resolvable
{
    /**
     * Caches reflected controller routes for repeated reverse-routing lookups.
     *
     * @var Map<array{int, string, string}>
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    private readonly Map $resolvedRoutes;

    /**
     * Returns the path of a route.
     *
     * @param array $handler
     *
     * @return string
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function path(array $handler): string
    {
        if (!isset($handler[0]) || !isset($handler[1]) || !class_exists($handler[0]) || !method_exists($handler[0], $handler[1])) {
            throw new InvalidHandlerException();
        }

        foreach ($this->staticRoutes as $path => $routes) {
            foreach ($routes as $route) {
                foreach ($route->frames as $frame) {
                    if (!($frame instanceof RouteFrame)) {
                        continue;
                    }

                    if ($frame->route->class === $handler[0] && $frame->route->method === $handler[1]) {
                        return $path;
                    }
                }
            }
        }

        $dynamicRoutes = array_merge(...$this->dynamicRoutes);

        foreach ($dynamicRoutes as $path => $routes) {
            unset($routes['segments']);

            foreach ($routes as $route) {
                foreach ($route->frames as $frame) {
                    if (!($frame instanceof RouteFrame)) {
                        continue;
                    }

                    if ($frame->route->class === $handler[0] && $frame->route->method === $handler[1]) {
                        return $path;
                    }
                }
            }
        }

        throw new InvalidHandlerException();
    }

    /**
     * Builds the first matching registered template. Method and template select among multiple routes.
     *
     * @param array{class-string, string} $handler
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $query
     * @param HttpMethod|null $method
     * @param string|null $template
     *
     * @return string
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function url(
        array $handler,
        array $parameters = [],
        array $query = [],
        ?HttpMethod $method = null,
        ?string $template = null
    ): string
    {
        if (!isset($handler[0], $handler[1]) || !is_string($handler[0]) || !is_string($handler[1])) {
            throw new InvalidHandlerException();
        }

        $groups = [...array_values($this->staticRoutes)];

        foreach ($this->dynamicRoutes as $routes) {
            foreach ($routes as $mapping) {
                $groups[] = $mapping;
            }
        }

        foreach ($groups as $mapping) {
            foreach ($mapping as $stack) {
                if (!$stack instanceof FrameStack || ($method !== null && $stack->method !== $method) || ($template !== null && $stack->pathPlain !== $template)) {
                    continue;
                }

                $matches = false;
                $injectables = [];

                foreach ($stack->frames as $frame) {
                    if ($frame instanceof ControllerFrame) {
                        foreach ($frame->controller->parameters as $parameter) {
                            $injectables[$parameter->name] = $parameter;
                        }
                    } elseif ($frame instanceof RouteFrame) {
                        $matches = [$frame->route->class, $frame->route->method] === $handler;

                        foreach ($frame->route->parameters as $parameter) {
                            $injectables[$parameter->name] = $parameter;
                        }
                    }
                }

                if (!$matches) {
                    continue;
                }

                $used = [];
                $path = preg_replace_callback('/(?:\/)?\$([a-zA-Z_][a-zA-Z0-9_]*)/', static function (array $match) use ($parameters, $injectables, &$used): string {
                    $name = $match[1];
                    $used[$name] = true;

                    if (!array_key_exists($name, $parameters)) {
                        if ($injectables[$name]->defaultValue->defined ?? false) {
                            return '';
                        }

                        throw new InvalidRouteParametersException("Missing route parameter {$name}.");
                    }

                    $value = $parameters[$name];

                    if ($value instanceof BackedEnum) {
                        $value = $value->value;
                    } elseif ($value instanceof StringParsableInterface) {
                        $value = $value instanceof JsonSerializable ? $value->jsonSerialize() : (string)$value;
                    } elseif (is_bool($value)) {
                        $value = $value ? 'true' : 'false';
                    }

                    if (!is_string($value) && !is_int($value) && !is_float($value)) {
                        throw new InvalidRouteParametersException("Route parameter {$name} cannot be encoded.");
                    }

                    return (str_starts_with($match[0], '/') ? '/' : '') . rawurlencode((string)$value);
                }, $stack->pathPlain);

                if (array_diff_key($parameters, $used) !== []) {
                    throw new InvalidRouteParametersException('Unknown route parameters.');
                }

                $path = $path === '' ? '/' : $path;

                if (preg_match('#^' . $stack->path . '$#D', RouterUtil::normalizePath(RouterUtil::decodePath($path))) !== 1) {
                    throw new InvalidRouteParametersException('Route parameters do not match their declared types.');
                }

                $suffix = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

                return $path . ($suffix !== '' ? '?' . $suffix : '');
            }
        }

        throw new InvalidHandlerException();
    }

    /**
     * Turns the request into a response.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function resolve(HttpRequest $request): HttpResponse
    {
        $pathName = RouterUtil::normalizePath(RouterUtil::decodePath($request->pathName));
        $resolved = $this->resolvedRoutes;

        if ($this instanceof DynamicRouter && !isset($this->combinedDynamicRegexes[count(RouterUtil::pathToSegments($pathName))])) {
            $this->compile();
        }

        if (isset($this->staticRoutes[$pathName])) {
            return $this->handle($request, $this->staticRoutes[$pathName]);
        }

        $cacheKey = $request->method->name . $pathName;

        if ($resolved->has($cacheKey)) {
            [$segmentCount, $route, $regex] = $resolved->get($cacheKey);

            if (!preg_match($regex, $pathName, $parameters)) {
                return new NotFoundHttpResponse();
            }

            return $this->handle($request, $this->dynamicRoutes[$segmentCount][$route], $parameters);
        }

        if (empty($this->dynamicRoutes)) {
            return new NotFoundHttpResponse();
        }

        $segments = RouterUtil::pathToSegments($pathName);
        $segmentCount = count($segments);

        if (!isset($this->dynamicRoutes[$segmentCount]) || empty($this->dynamicRoutes[$segmentCount])) {
            return new NotFoundHttpResponse();
        }

        [$combinedRegex, $keys] = $this->combinedDynamicRegexes[$segmentCount];

        if (!preg_match($combinedRegex, $pathName, $parameters)) {
            return new NotFoundHttpResponse();
        }

        $route = $keys[(int)$parameters['MARK']];

        // Remove MARK (PCRE control verb) and empty strings produced by
        // non-matching alternatives in the combined pattern.
        $parameters = array_filter($parameters, static fn(mixed $v, string $k) => $k !== 'MARK' && (!is_string($k) || $v !== ''), ARRAY_FILTER_USE_BOTH);
        $resolved->set($cacheKey, [$segmentCount, $route, "#^{$route}\$#"]);

        if (count($resolved) > 1024) {
            $removed = 0;

            foreach ($resolved as $key => $_) {
                $resolved->unset($key);

                if (++$removed === 512) {
                    break;
                }
            }
        }

        return $this->handle($request, $this->dynamicRoutes[$segmentCount][$route], $parameters);
    }

    /**
     * Handles the request using the given route.
     *
     * @param HttpRequest $request
     * @param array $mapping
     * @param array $parameters
     *
     * @return HttpResponse
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    private function handle(
        HttpRequest $request,
        array $mapping,
        array $parameters = []
    ): HttpResponse
    {
        $methodKey = $request->method->name;
        $preflight = false;
        $allowedMethods = array_keys(array_diff_key($mapping, ['segments' => null]));

        if (!isset($mapping[$methodKey])) {
            if ($request->method === HttpMethod::OPTIONS) {
                $methodKey = strtoupper($request->headers->get('access-control-request-method') ?? array_key_first(array_diff_key($mapping, ['segments' => null])));
                $preflight = true;
            } else {
                $methodKey = HttpMethod::ANY->name;
            }

            if (!isset($mapping[$methodKey])) {
                $allowedMethods = array_keys(array_diff_key($mapping, ['segments' => null]));

                return new MethodNotAllowedHttpResponse($allowedMethods);
            }
        }

        if (!empty($parameters)) {
            $request->parameters->merge(array_map(static fn(mixed $value): mixed => is_string($value) ? rawurldecode($value) : $value, $parameters));
        }

        return new Runner($this, $mapping[$methodKey], $preflight ? $allowedMethods : null)
            ->run($request);
    }
}
