<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Foundation\Collection\Map;
use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Structure\{HttpCookiesMap, HttpFilesMap, HttpHeadersMap, HttpPostMap, HttpQueryMap, HttpServerMap};
use Raxos\Router\Error\{MappingException, RuntimeException};
use Raxos\Router\Frame\FrameStack;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{NotFoundResponse, Response};
use function array_filter;
use function array_key_first;
use function array_keys;
use function preg_match;
use function rtrim;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function usort;
use const ARRAY_FILTER_USE_KEY;

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 */
readonly class Router
{

    public Map $globals;

    /**
     * Router constructor.
     *
     * @param array<string, array<string, FrameStack>> $mapping
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public array $mapping = [],
    )
    {
        $this->globals = new Map();
        $this->globals->set('router', $this);
    }

    /**
     * Creates a router request.
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
     * @since 1.1.0
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
        $request = HttpRequest::createFromGlobals();

        $method ??= $request->method;
        $uri ??= $request->uri;

        return Request::create(
            method: $method,
            uri: $uri,
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
     * @since 1.1.0
     */
    public function resolve(Request $request): Response
    {
        $routes = array_keys($this->mapping);
        $routes = array_filter($routes, static fn(string $route) => str_starts_with($request->pathName, rtrim(substr($route, 0, strpos($route, '(') ?: strlen($route)), '?')));

        usort($routes, RouterUtil::routeSorter(...));

        foreach ($routes as $route) {
            $isCandidate = $route === $request->pathName || preg_match("#^{$route}$#", $request->pathName, $parameters);

            if (!$isCandidate) {
                continue;
            }

            $methodKey = $request->method->name;

            if (!isset($this->mapping[$route][$methodKey])) {
                if ($request->method === HttpMethod::OPTIONS) {
                    $methodKey = array_key_first($this->mapping[$route]);
                } else {
                    $methodKey = HttpMethod::ANY->name;
                }
            }

            if (!isset($this->mapping[$route][$methodKey])) {
                continue;
            }

            $parameters ??= [];
            $parameters = array_filter($parameters, is_string(...), ARRAY_FILTER_USE_KEY);
            $request->parameters->merge($parameters);

            $stack = $this->mapping[$route][$methodKey];
            $runner = new Runner($this, $stack);

            return $runner->run($request);
        }

        return new NotFoundResponse();
    }

    /**
     * Creates a router with the given controllers.
     *
     * @param class-string[] $controllers
     *
     * @return self
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function createFromControllers(array $controllers): self
    {
        return new self(Mapper::for($controllers));
    }

    /**
     * Returns a router with the given mapping.
     *
     * @param array<string, array<string, FrameStack>> $mapping
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function createFromMapping(array $mapping): self
    {
        return new self($mapping);
    }

}
