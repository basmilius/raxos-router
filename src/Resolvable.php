<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Contract\Router\{RouterInterface, RuntimeExceptionInterface};
use Raxos\Http\{HttpMethod, HttpRequest, HttpResponse};
use Raxos\Http\Response\{MethodNotAllowedHttpResponse, NotFoundHttpResponse};
use Raxos\Router\Error\InvalidHandlerException;
use Raxos\Router\Frame\RouteFrame;
use function array_diff_key;
use function array_filter;
use function array_key_first;
use function array_keys;
use function array_merge;
use function array_slice;
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
        static $resolved = [];

        if (isset($this->staticRoutes[$request->pathName])) {
            return $this->handle($request, $this->staticRoutes[$request->pathName]);
        }

        $cacheKey = $request->method->name . $request->pathName;

        if (isset($resolved[$cacheKey])) {
            [$segmentCount, $route, $regex] = $resolved[$cacheKey];

            if (!preg_match($regex, $request->pathName, $parameters)) {
                return new NotFoundHttpResponse();
            }

            return $this->handle($request, $this->dynamicRoutes[$segmentCount][$route], $parameters);
        }

        if (empty($this->dynamicRoutes)) {
            return new NotFoundHttpResponse();
        }

        $segments = RouterUtil::pathToSegments($request->pathName);
        $segmentCount = count($segments);

        if (!isset($this->dynamicRoutes[$segmentCount]) || empty($this->dynamicRoutes[$segmentCount])) {
            return new NotFoundHttpResponse();
        }

        [$combinedRegex, $keys] = $this->combinedDynamicRegexes[$segmentCount];

        if (!preg_match($combinedRegex, $request->pathName, $parameters)) {
            return new NotFoundHttpResponse();
        }

        $route = $keys[(int)$parameters['MARK']];

        // Remove MARK (PCRE control verb) and empty strings produced by
        // non-matching alternatives in the combined pattern.
        $parameters = array_filter($parameters, static fn($v, $k) => $k !== 'MARK' && (!is_string($k) || $v !== ''), ARRAY_FILTER_USE_BOTH);
        $resolved[$cacheKey] = [$segmentCount, $route, "#^{$route}\$#"];

        if (count($resolved) > 1024) {
            $resolved = array_slice($resolved, 512, preserve_keys: true);
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

        if (!isset($mapping[$methodKey])) {
            if ($request->method === HttpMethod::OPTIONS) {
                $methodKey = strtoupper($request->headers->get('access-control-request-method') ?? array_key_first(array_diff_key($mapping, ['segments' => null])));
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

        return new Runner($this, $mapping[$methodKey])
            ->run($request);
    }

}
