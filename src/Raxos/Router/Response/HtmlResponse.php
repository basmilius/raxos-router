<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use JetBrains\PhpStorm\Language;
use Raxos\Http\{HttpHeader, HttpHeaders, HttpResponseCode};

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
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        #[Language('HTML')] public string $body,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    )
    {
        parent::__construct(
            $headers->set(HttpHeader::CONTENT_TYPE, 'text/html'),
            $responseCode
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function send(): void
    {
        parent::send();

        echo $this->body;
    }

}
