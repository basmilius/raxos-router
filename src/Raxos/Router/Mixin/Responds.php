<?php
declare(strict_types=1);

namespace Raxos\Router\Mixin;

use Exception;
use JetBrains\PhpStorm\Language;
use JsonSerializable;
use Raxos\Http\HttpResponseCode;
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{BinaryResponse, FileResponse, ForbiddenResponse, HtmlResponse, JsonResponse, NoContentResponse, NotFoundResponse, RedirectResponse, ResultResponse};

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
     * Returns a binary response.
     *
     * @param string $data
     * @param HttpHeadersMap $headers
     *
     * @return BinaryResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function binary(
        string $data,
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): BinaryResponse
    {
        return new BinaryResponse($data, $headers);
    }

    /**
     * Returns an error response using the given JSON serializable exception.
     *
     * @param Exception&JsonSerializable $err
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function error(
        Exception&JsonSerializable $err
    ): JsonResponse
    {
        $responseCode = HttpResponseCode::INTERNAL_SERVER_ERROR;

        if (isset($err->responseCode)) {
            $responseCode = $err->responseCode;
        }

        return $this->json($err, responseCode: $responseCode);
    }

    /**
     * Returns a file response.
     *
     * @param string $path
     * @param Request $request
     * @param HttpHeadersMap $headers
     *
     * @return FileResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function file(
        string $path,
        Request $request,
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): FileResponse
    {
        return new FileResponse($path, $request, $headers);
    }

    /**
     * Returns a forbidden response.
     *
     * @param HttpHeadersMap $headers
     *
     * @return ForbiddenResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function forbidden(
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): ForbiddenResponse
    {
        return new ForbiddenResponse($headers);
    }

    /**
     * Returns a html response.
     *
     * @param string $body
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return HtmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function html(
        #[Language('HTML')] string $body,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): HtmlResponse
    {
        return new HtmlResponse($body, $headers, $responseCode);
    }

    /**
     * Returns a json response.
     *
     * @param mixed $body
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function json(
        mixed $body,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): JsonResponse
    {
        return new JsonResponse($body, $headers, $responseCode);
    }

    /**
     * Returns a no content response.
     *
     * @param HttpHeadersMap $headers
     *
     * @return NoContentResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function noContent(
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): NoContentResponse
    {
        return new NoContentResponse($headers);
    }

    /**
     * Returns a not found response.
     *
     * @return NotFoundResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function notFound(): NotFoundResponse
    {
        return new NotFoundResponse();
    }

    /**
     * Returns a redirect response.
     *
     * @param string $destination
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return RedirectResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function redirect(
        string $destination,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::FOUND
    ): RedirectResponse
    {
        return new RedirectResponse($destination, $headers, $responseCode);
    }

    /**
     * Returns a result response.
     *
     * @param mixed $result
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return ResultResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    protected function result(
        mixed $result,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): ResultResponse
    {
        return new ResultResponse($result, $headers, $responseCode);
    }

}
