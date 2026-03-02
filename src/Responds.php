<?php
declare(strict_types=1);

namespace Raxos\Router;

use Exception;
use JetBrains\PhpStorm\Language;
use JsonSerializable;
use Raxos\Http\{HttpRequest, HttpResponse, HttpResponseCode};
use Raxos\Http\Response\{BinaryHttpResponse, FileHttpResponse, ForbiddenHttpResponse, HtmlHttpResponse, JsonHttpResponse, NoContentHttpResponse, NotFoundHttpResponse, RedirectHttpResponse, ResultHttpResponse};
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Http\Validate\Error\{ConstraintErrorException, ValidationNotOkException};
use Throwable;

/**
 * Trait Responds
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 2.1.0
 */
trait Responds
{

    /**
     * Returns a binary response.
     *
     * @param string $data
     * @param HttpHeadersMap $headers
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function binary(
        string $data,
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): HttpResponse
    {
        return new BinaryHttpResponse($data, $headers);
    }

    /**
     * Returns an error response using the given JSON serializable exception.
     *
     * @param Exception&JsonSerializable $err
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function error(
        Throwable&JsonSerializable $err
    ): HttpResponse
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
     * @param HttpRequest $request
     * @param HttpHeadersMap $headers
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function file(
        string $path,
        HttpRequest $request,
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): HttpResponse
    {
        return new FileHttpResponse($path, $request, $headers);
    }

    /**
     * Returns a forbidden response.
     *
     * @param HttpHeadersMap $headers
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function forbidden(
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): HttpResponse
    {
        return new ForbiddenHttpResponse($headers);
    }

    /**
     * Returns an HTML response.
     *
     * @param string $body
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function html(
        #[Language('HTML')] string $body,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): HttpResponse
    {
        return new HtmlHttpResponse($body, $headers, $responseCode);
    }

    /**
     * Returns a json response.
     *
     * @param mixed $body
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function json(
        mixed $body,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): HttpResponse
    {
        return new JsonHttpResponse($body, $headers, $responseCode);
    }

    /**
     * Returns a no-content response.
     *
     * @param HttpHeadersMap $headers
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function noContent(
        HttpHeadersMap $headers = new HttpHeadersMap()
    ): HttpResponse
    {
        return new NoContentHttpResponse($headers);
    }

    /**
     * Returns a not found response.
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function notFound(): HttpResponse
    {
        return new NotFoundHttpResponse();
    }

    /**
     * Returns a redirect response.
     *
     * @param string $destination
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function redirect(
        string $destination,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::FOUND
    ): HttpResponse
    {
        return new RedirectHttpResponse($destination, $headers, $responseCode);
    }

    /**
     * Returns a result response.
     *
     * @param mixed $result
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function result(
        mixed $result,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::OK
    ): HttpResponse
    {
        return new ResultHttpResponse($result, $headers, $responseCode);
    }

    /**
     * Returns a validation error for a single constraint.
     *
     * @param string $field
     * @param string $constraint
     * @param string $message
     * @param array $params
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function validationError(string $field, string $constraint, string $message, array $params = []): HttpResponse
    {
        return $this->json(
            new ValidationNotOkException([
                $field => new ConstraintErrorException("http_validation_constraint_{$constraint}", $message, $params)
            ])
        );
    }

    /**
     * Returns a validation error for multiple constraints.
     *
     * @param array $errors
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 2.1.0
     */
    protected function validationErrors(array ...$errors): HttpResponse
    {
        $result = [];

        foreach ($errors as $field => $constraintErrors) {
            foreach ($constraintErrors as $constraint => $message) {
                $result[$field] = new ConstraintErrorException("http_validation_constraint_{$constraint}", $message);
            }
        }

        return $this->json(
            new ValidationNotOkException($result)
        );
    }

}
