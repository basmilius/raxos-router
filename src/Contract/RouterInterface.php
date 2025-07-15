<?php
declare(strict_types=1);

namespace Raxos\Router\Contract;

use Raxos\Foundation\Collection\Map;
use Raxos\Http\HttpMethod;
use Raxos\Http\Structure\{HttpCookiesMap, HttpFilesMap, HttpHeadersMap, HttpPostMap, HttpQueryMap, HttpServerMap};
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;

/**
 * Interface RouterInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Contract
 * @since 1.5.0
 */
interface RouterInterface
{

    public Map $globals {
        get;
    }

    public array $dynamicRoutes {
        get;
    }

    public array $staticRoutes {
        get;
    }

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
    ): Request;

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
    public function resolve(Request $request): Response;

}
