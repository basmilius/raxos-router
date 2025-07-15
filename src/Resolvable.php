<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Http\HttpMethod;
use Raxos\Http\Structure\{HttpCookiesMap, HttpFilesMap, HttpHeadersMap, HttpPostMap, HttpQueryMap, HttpServerMap};
use Raxos\Router\Contract\RouterInterface;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{NotFoundResponse, Response};
use function array_filter;
use function array_key_first;
use function min;
use function preg_match;
use function str_starts_with;
use function strlen;
use function strpos;
use function strtoupper;
use function substr;

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
        $routes = [];

        if (isset($this->staticRoutes[$request->pathName])) {
            return $this->handle($request, $this->staticRoutes[$request->pathName]);
        }

        foreach ($this->dynamicRoutes as $route => $_) {
            $pos = array_filter([
                strpos($route, '?'),
                strpos($route, '/'),
                strlen($route)
            ]);

            $static = substr($route, 0, min($pos) - 1);

            if (!str_starts_with($request->pathName, $static)) {
                continue;
            }

            $routes[] = $route;
        }

        foreach ($routes as $route) {
            if (!preg_match('#^' . $route . '$#', $request->pathName, $parameters)) {
                continue;
            }

            return $this->handle($request, $this->dynamicRoutes[$route], $parameters);
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

        $request->parameters->merge($parameters);

        return new Runner($this, $mapping[$methodKey])
            ->run($request);
    }

}
