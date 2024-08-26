<?php
declare(strict_types=1);

namespace Raxos\Router\Request;

use Raxos\Foundation\Collection\Map;
use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Store\{HttpCookieStore, HttpFileStore, HttpHeaderStore, HttpPostStore, HttpQueryStore, HttpServerStore};
use function explode;
use function strstr;

/**
 * Class Request
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Request
 * @since 1.1.0
 */
final readonly class Request extends HttpRequest
{

    /**
     * HttpRequest constructor.
     *
     * @param HttpCookieStore $cookies
     * @param HttpFileStore $files
     * @param HttpHeaderStore $headers
     * @param HttpPostStore $post
     * @param HttpQueryStore $queryString
     * @param HttpServerStore $server
     * @param HttpMethod $method
     * @param string $pathName
     * @param string $uri
     * @param Map $parameters
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        HttpCookieStore $cookies,
        HttpFileStore $files,
        HttpHeaderStore $headers,
        HttpPostStore $post,
        HttpQueryStore $queryString,
        HttpServerStore $server,
        HttpMethod $method,
        string $pathName,
        string $uri,
        public Map $parameters
    )
    {
        parent::__construct($cookies, $files, $headers, $post, $queryString, $server, $method, $pathName, $uri);
    }

    /**
     * Adds a query parameter as request parameter.
     *
     * @param string $name
     * @param string $key
     * @param callable|null $sanitizer
     * @param mixed|null $defaultValue
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function addQuery(string $name, string $key, ?callable $sanitizer = null, mixed $defaultValue = null): self
    {
        if (!$this->query->has($key)) {
            if ($defaultValue !== null) {
                $this->parameters->set($name, $defaultValue);
            }

            return $this;
        }

        $value = $this->query->get($key);

        if ($sanitizer !== null) {
            $value = $sanitizer($value);
        }

        $this->parameters->set($name, $value);

        return $this;
    }

    /**
     * Creates a request for the router.
     *
     * @param HttpMethod $method
     * @param string $uri
     * @param Map $parameters
     * @param HttpCookieStore|null $cookies
     * @param HttpFileStore|null $files
     * @param HttpHeaderStore|null $headers
     * @param HttpPostStore|null $post
     * @param HttpQueryStore|null $query
     * @param HttpServerStore|null $server
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function create(
        HttpMethod $method,
        string $uri,
        Map $parameters = new Map(),
        ?HttpCookieStore $cookies = null,
        ?HttpFileStore $files = null,
        ?HttpHeaderStore $headers = null,
        ?HttpPostStore $post = null,
        ?HttpQueryStore $query = null,
        ?HttpServerStore $server = null
    ): self
    {
        $pathName = strstr($uri, '?', true) ?: $uri;
        $queryString = explode('?', $uri)[1] ?? '';

        $cookies ??= HttpCookieStore::fromGlobals();
        $files ??= HttpFileStore::fromGlobals();
        $headers ??= HttpHeaderStore::fromGlobals();
        $post ??= HttpPostStore::fromGlobals();
        $query ??= HttpQueryStore::fromString($queryString);
        $server ??= HttpServerStore::fromGlobals();

        return new self(
            $cookies,
            $files,
            $headers,
            $post,
            $query,
            $server,
            $method,
            $pathName,
            $uri,
            $parameters
        );
    }

}
