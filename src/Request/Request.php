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
readonly class Request extends HttpRequest
{

    /**
     * Request constructor.
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
     * @param HttpCookiesMap $cookies
     * @param HttpFilesMap $files
     * @param HttpHeadersMap $headers
     * @param HttpPostMap $post
     * @param HttpQueryMap|null $query
     * @param HttpServerMap $server
     * @param Map $parameters
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function create(
        HttpMethod $method,
        string $uri,
        HttpCookiesMap $cookies,
        HttpFilesMap $files,
        HttpHeadersMap $headers,
        HttpPostMap $post,
        ?HttpQueryMap $query,
        HttpServerMap $server,
        Map $parameters = new Map()
    ): self
    {
        $pathName = strstr($uri, '?', true) ?: $uri;
        $queryString = explode('?', $uri)[1] ?? '';

        $query ??= HttpQueryMap::createFromString($queryString);

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
