<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeader, HttpResponseCode};
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Contract\ResponseInterface;
use function fastcgi_finish_request;
use function function_exists;
use function header;
use function http_response_code;
use function ob_end_flush;
use function ob_flush;
use function ob_start;

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
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public HttpHeadersMap $headers = new HttpHeadersMap(),
        public HttpResponseCode $responseCode = HttpResponseCode::OK
    ) {}

    /**
     * Adds the given response header.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function withHeader(string $name, string $value, bool $replace = false): static
    {
        if ($replace) {
            $this->headers->set($name, $value);
        } else {
            $this->headers->add($name, $value);
        }

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
        ob_start();

        $this->sendResponseCode();
        $this->sendHeaders();
        ob_flush();

        $this->sendBody();
        ob_end_flush();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Sends the response body to the browser.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 1.3.1
     */
    protected function sendBody(): void {}

    /**
     * Sends the response headers to the browser.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function sendHeaders(): void
    {
        if ($this->headers->has(HttpHeader::CONTENT_DISPOSITION)) {
            $this->withHeader(HttpHeader::ACCESS_CONTROL_EXPOSE_HEADERS, HttpHeader::CONTENT_DISPOSITION);
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
    protected function sendResponseCode(): void
    {
        http_response_code($this->responseCode->value);
    }

}
