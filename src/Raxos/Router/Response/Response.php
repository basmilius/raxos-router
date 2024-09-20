<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeader, HttpHeaders, HttpResponseCode};
use Raxos\Router\Contract\ResponseInterface;
use function header;
use function http_response_code;

/**
 * Class Response
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
abstract class Response implements ResponseInterface
{

    /**
     * Response constructor.
     *
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public HttpHeaders $headers = new HttpHeaders(),
        public HttpResponseCode $responseCode = HttpResponseCode::OK
    ) {}

    /**
     * Adds the given response header.
     *
     * @param HttpHeader|string $name
     * @param string $value
     * @param bool $replace
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function withHeader(HttpHeader|string $name, string $value, bool $replace = false): static
    {
        if ($replace) {
            $this->headers = $this->headers->set($name, $value);
        } else {
            $this->headers = $this->headers->add($name, $value);
        }

        return $this;
    }

    /**
     * Modify the response headers.
     *
     * @param callable(HttpHeaders):HttpHeaders $fn
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function withHeaders(callable $fn): static
    {
        $this->headers = $fn($this->headers);

        return $this;
    }

    /**
     * Modify the response code.
     *
     * @param HttpResponseCode $responseCode
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function withResponseCode(HttpResponseCode $responseCode): static
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function send(): void
    {
        $this->sendResponseCode();
        $this->sendHeaders();
    }

    /**
     * Sends the response headers to the browser.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected final function sendHeaders(): void
    {
        if ($this->headers->has(HttpHeader::CONTENT_DISPOSITION)) {
            $this->withHeader(HttpHeader::ACCESS_CONTROL_EXPOSE_HEADERS, HttpHeader::CONTENT_DISPOSITION->value);
        }

        foreach ($this->headers as $name => $values) {
            foreach ($values as $index => $value) {
                header("{$name}: {$value}", $index === 0);
            }
        }
    }

    /**
     * Sends the response code to the browser.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected final function sendResponseCode(): void
    {
        http_response_code($this->responseCode->value);
    }

}
