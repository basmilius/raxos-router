<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;
use function array_key_exists;
use function is_array;

/**
 * Class ResponseRegistry
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.0
 */
final class ResponseRegistry
{

    private array $headers = [];
    private HttpResponseCode $responseCode = HttpResponseCode::OK;

    /**
     * Adds the given header to the response.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function header(string $name, string $value, bool $replace = true): self
    {
        if (array_key_exists($name, $this->headers) && !$replace) {
            if (is_array($this->headers[$name])) {
                $this->headers[$name][] = $value;
            } else {
                $this->headers[$name] = [$this->headers[$name], $value];
            }
        } else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Sets the response code.
     *
     * @param HttpResponseCode $code
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function responseCode(HttpResponseCode $code): self
    {
        $this->responseCode = $code;

        return $this;
    }

    /**
     * Gets the response headers.
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Gets the response code.
     *
     * @return HttpResponseCode
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getResponseCode(): HttpResponseCode
    {
        return $this->responseCode;
    }

    /**
     * Returns true if the given header is registred.
     *
     * @param string $name
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

}
