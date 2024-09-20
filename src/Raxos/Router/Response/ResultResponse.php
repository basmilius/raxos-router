<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeaders, HttpResponseCode};
use Raxos\Router\Error\RuntimeException;

/**
 * Class ResultResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class ResultResponse extends Response
{

    /**
     * JsonResponse constructor.
     *
     * @param mixed $result
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public mixed $result,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    )
    {
        parent::__construct(
            $headers,
            $responseCode
        );
    }

    /**
     * Turns the result response into a JSON response.
     *
     * @return HtmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function asHtml(): HtmlResponse
    {
        return new HtmlResponse((string)$this->result, $this->headers, $this->responseCode);
    }

    /**
     * Turns the result response into a JSON response.
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function asJson(): JsonResponse
    {
        return new JsonResponse($this->result, $this->headers, $this->responseCode);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function send(): void
    {
        throw RuntimeException::resultResponse();
    }

}
