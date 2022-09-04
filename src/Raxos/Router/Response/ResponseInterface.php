<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;

/**
 * Interface ResponseInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.2
 */
interface ResponseInterface
{

    /**
     * Gets the http code for the response.
     *
     * @return HttpResponseCode
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getResponseCode(): HttpResponseCode;

    /**
     * Prepares the response body.
     *
     * @return string|false
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.2
     */
    public function prepareBody(): string|false;

    /**
     * Prepares the response headers.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.2
     */
    public function prepareHeaders(): void;

    /**
     * Sends the response to browser.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function respond(): void;

}
