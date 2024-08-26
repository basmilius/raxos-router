<?php
declare(strict_types=1);

namespace Raxos\Router\Mixin;

use Exception;
use JetBrains\PhpStorm\Language;
use JsonSerializable;
use Raxos\Http\{HttpHeaders, HttpResponseCode};
use Raxos\Router\Response\{ForbiddenResponse, HtmlResponse, JsonResponse, NoContentResponse, RedirectResponse, ResultResponse};

/**
 * Trait Responds
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Mixin
 * @since 1.1.0
 */
trait Responds
{

    /**
     * Returns an error response using the given json serializable exception.
     *
     * @param Exception&JsonSerializable $err
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function error(Exception&JsonSerializable $err): JsonResponse
    {
        $responseCode = HttpResponseCode::INTERNAL_SERVER_ERROR;

        if (isset($err->responseCode)) {
            $responseCode = $err->responseCode;
        }

        return $this->json($err, responseCode: $responseCode);
    }

    /**
     * Returns a forbidden response.
     *
     * @param HttpHeaders $headers
     *
     * @return ForbiddenResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function forbidden(
        HttpHeaders $headers = new HttpHeaders()
    ): ForbiddenResponse
    {
        return new ForbiddenResponse($headers);
    }

    /**
     * Returns a html response.
     *
     * @param string $body
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @return HtmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function html(
        #[Language('HTML')] string $body,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): HtmlResponse
    {
        return new HtmlResponse($body, $headers, $responseCode);
    }

    /**
     * Returns a json response.
     *
     * @param mixed $body
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function json(
        mixed $body,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): JsonResponse
    {
        return new JsonResponse($body, $headers, $responseCode);
    }

    /**
     * Returns a no content response.
     *
     * @param HttpHeaders $headers
     *
     * @return NoContentResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function noContent(
        HttpHeaders $headers = new HttpHeaders()
    ): NoContentResponse
    {
        return new NoContentResponse($headers);
    }

    /**
     * Returns a redirect response.
     *
     * @param string $destination
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @return RedirectResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function redirect(
        string $destination,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::FOUND
    ): RedirectResponse
    {
        return new RedirectResponse($destination, $headers, $responseCode);
    }

    /**
     * Returns a result response.
     *
     * @param mixed $result
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @return ResultResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function result(
        mixed $result,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): ResultResponse
    {
        return new ResultResponse($result, $headers, $responseCode);
    }

}
