<?php
declare(strict_types=1);

namespace Raxos\Router\Request;

use Raxos\Foundation\Collection\Map;
use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Structure\{HttpCookiesMap, HttpFilesMap, HttpHeadersMap, HttpPostMap, HttpQueryMap, HttpServerMap};
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
     * @param HttpCookiesMap $cookies
     * @param HttpFilesMap $files
     * @param HttpHeadersMap $headers
     * @param HttpPostMap $post
     * @param HttpQueryMap $queryString
     * @param HttpServerMap $server
     * @param HttpMethod $method
     * @param string $pathName
     * @param string $uri
     * @param Map $parameters
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        HttpCookiesMap $cookies,
        HttpFilesMap $files,
        HttpHeadersMap $headers,
        HttpPostMap $post,
        HttpQueryMap $queryString,
        HttpServerMap $server,
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
     * @param HttpCookiesMap|null $cookies
     * @param HttpFilesMap|null $files
     * @param HttpHeadersMap|null $headers
     * @param HttpPostMap|null $post
     * @param HttpQueryMap|null $query
     * @param HttpServerMap|null $server
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function create(
        HttpMethod $method,
        string $uri,
        Map $parameters = new Map(),
        ?HttpCookiesMap $cookies = null,
        ?HttpFilesMap $files = null,
        ?HttpHeadersMap $headers = null,
        ?HttpPostMap $post = null,
        ?HttpQueryMap $query = null,
        ?HttpServerMap $server = null
    ): self
    {
        $pathName = strstr($uri, '?', true) ?: $uri;
        $queryString = explode('?', $uri)[1] ?? '';

        $cookies ??= HttpCookiesMap::createFromGlobals();
        $files ??= HttpFilesMap::createFromGlobals();
        $headers ??= HttpHeadersMap::createFromGlobals();
        $post ??= HttpPostMap::createFromGlobals();
        $query ??= HttpQueryMap::createFromString($queryString);
        $server ??= HttpServerMap::createFromGlobals();

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
