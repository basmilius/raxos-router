<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use JsonException;
use Raxos\Http\{HttpHeader, HttpResponseCode};
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Error\RuntimeException;
use function json_encode;
use const JSON_BIGINT_AS_STRING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;

/**
 * Class JsonResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class JsonResponse extends Response
{

    /**
     * JsonResponse constructor.
     *
     * @param mixed $body
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public mixed $body,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    )
    {
        $headers->set(HttpHeader::CONTENT_TYPE, 'application/json');

        parent::__construct(
            $headers,
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

        try {
            echo json_encode($this->body, JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            throw RuntimeException::unexpected($err, __METHOD__);
        }
    }

}
