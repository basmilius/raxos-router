<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use JetBrains\PhpStorm\Language;
use Raxos\Http\{HttpHeader, HttpResponseCode};
use Raxos\Http\Structure\HttpHeadersMap;

/**
 * Class HtmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class HtmlResponse extends Response
{

    /**
     * HtmlResponse constructor.
     *
     * @param string $body
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        #[Language('HTML')] public string $body,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    )
    {
        $headers->set(HttpHeader::CONTENT_TYPE, 'text/html');

        parent::__construct(
            $headers,
            $responseCode
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.3.1
     */
    protected function sendBody(): void
    {
        echo $this->body;
    }

}
