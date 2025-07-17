<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Http\HttpMethod;
use Raxos\Http\Structure\{HttpCookiesMap, HttpFilesMap, HttpHeadersMap, HttpPostMap, HttpQueryMap, HttpServerMap};
use Raxos\Router\Contract\RouterInterface;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{NotFoundResponse, Response};
use function array_key_first;
use function count;
use function preg_match;
use function strtoupper;

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
     * Returns a router request.
     *
     * @param HttpCookiesMap|null $cookies
     * @param HttpFilesMap|null $files
     * @param HttpHeadersMap|null $headers
     * @param HttpPostMap|null $post
     * @param HttpQueryMap|null $query
     * @param HttpServerMap|null $server
     * @param HttpMethod|null $method
     * @param string|null $uri
     *
     * @return Request
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function request(
        ?HttpCookiesMap $cookies = null,
        ?HttpFilesMap $files = null,
        ?HttpHeadersMap $headers = null,
        ?HttpPostMap $post = null,
        ?HttpQueryMap $query = null,
        ?HttpServerMap $server = null,
        ?HttpMethod $method = null,
        ?string $uri = null
    ): Request
    {
        static $request = null;

        $request ??= Request::createFromGlobals();

        return Request::create(
            method: $method ?? $request->method,
            uri: $uri ?? $request->uri,
            cookies: $cookies ?? $request->cookies,
            files: $files ?? $request->files,
            headers: $headers ?? $request->headers,
            post: $post ?? $request->post,
            query: $query ?? $request->query,
            server: $server ?? $request->server
        );
    }

    /**
     * Turns the request into a response.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function resolve(Request $request): Response
    {
        static $resolved = [];

        if (isset($this->staticRoutes[$request->pathName])) {
            return $this->handle($request, $this->staticRoutes[$request->pathName]);
        }

        if (isset($resolved[$request->method->name . $request->pathName])) {
            [$segmentCount, $route] = $resolved[$request->method->name . $request->pathName];

            if (!preg_match("#^{$route}\$#", $request->pathName, $parameters)) {
                return new NotFoundResponse();
            }

            return $this->handle($request, $this->dynamicRoutes[$segmentCount][$route], $parameters);
        }

        if (empty($this->dynamicRoutes)) {
            return new NotFoundResponse();
        }

        $segments = RouterUtil::pathToSegments($request->pathName);
        $segmentCount = count($segments);

        if (!isset($this->dynamicRoutes[$segmentCount]) || empty($this->dynamicRoutes[$segmentCount])) {
            return new NotFoundResponse();
        }

        $candidates = $this->dynamicRoutes[$segmentCount];
        $matches = [];

        if (empty($candidates)) {
            return new NotFoundResponse();
        }

        foreach ($candidates as $candidate => $route) {
            if (!$this->isCandidate($segments, $route['segments'])) {
                continue;
            }

            $matches[$candidate] = $route;
        }

        foreach ($matches as $route => $data) {
            if (!preg_match("#^{$route}\$#", $request->pathName, $parameters)) {
                continue;
            }

            $resolved[$request->method->name . $request->pathName] = [$segmentCount, $route];

            return $this->handle($request, $data, $parameters);
        }

        return new NotFoundResponse();
    }

    /**
     * Handles the request using the given route.
     *
     * @param Request $request
     * @param array $mapping
     * @param array $parameters
     *
     * @return Response
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    private function handle(Request $request, array $mapping, array $parameters = []): Response
    {
        $methodKey = $request->method->name;

        if (!isset($mapping[$methodKey])) {
            if ($request->method === HttpMethod::OPTIONS) {
                $methodKey = strtoupper($request->headers->get('access-control-request-method') ?? array_key_first($mapping));
            } else {
                $methodKey = HttpMethod::ANY->name;
            }

            if (!isset($mapping[$methodKey])) {
                return new NotFoundResponse();
            }
        }

        if (!empty($parameters)) {
            $request->parameters->merge($parameters);
        }

        return new Runner($this, $mapping[$methodKey])
            ->run($request);
    }

    /**
     * Returns TRUE if the request segments are a proper candidate.
     *
     * @param string[] $requestSegments
     * @param string[] $routeSegments
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function isCandidate(array $requestSegments, array $routeSegments): bool
    {
        for ($i = 0; $i < count($requestSegments); ++$i) {
            $requestSegment = $requestSegments[$i];
            $routeSegment = $routeSegments[$i];

            if (($routeSegment[0] ?? null) === '(') {
                continue;
            }

            if ($requestSegment !== $routeSegment) {
                return false;
            }
        }

        return true;
    }

}
