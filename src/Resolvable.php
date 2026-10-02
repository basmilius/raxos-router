<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Collection\Map;
use Raxos\Contract\Router\{RouterInterface, RuntimeExceptionInterface};
use Raxos\Http\Response\{MethodNotAllowedHttpResponse, NoContentHttpResponse, NotFoundHttpResponse};
use Raxos\Http\{HttpMethod, HttpRequest, HttpResponse};
use Raxos\Router\Error\InvalidHandlerException;
use Raxos\Router\Frame\RouteFrame;
use function array_diff_key;
use function array_filter;
use function array_key_first;
use function array_keys;
use function array_merge;
use function class_exists;
use function count;
use function is_string;
use function method_exists;
use function preg_match;
use function strtoupper;
use const ARRAY_FILTER_USE_BOTH;

/**
 * Trait Resolvable
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
        $pathName = RouterUtil::normalizePath($request->pathName);
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
    private function handle(HttpRequest $request, array $mapping, array $parameters = []): HttpResponse
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
            $request->parameters->merge($parameters);
        }

        return new Runner($this, $mapping[$methodKey], $preflight ? $allowedMethods : null)
            ->run($request);
    }

}
