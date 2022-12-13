<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Effect\RedirectEffect;
use Raxos\Router\Effect\VoidEffect;
use Raxos\Router\Router;

/**
 * Trait ResponseMethods
 *
 * @property Router $router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Controller
 * @since 1.0.0
 */
trait ResponseMethods
{

    /**
     * Adds the given header to the response.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function header(string $name, string $value, bool $replace = true): static
    {
        $this->router
            ->getResponseRegistry()
            ->header($name, $value, $replace);

        return $this;
    }

    /**
     * Returns a HTML response.
     *
     * @param mixed $value
     * @param HttpResponseCode $responseCode
     *
     * @return HtmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function html(string $value, HttpResponseCode $responseCode = HttpResponseCode::OK): HtmlResponse
    {
        $this->responseCode($responseCode);

        return new HtmlResponse($this->router, $value);
    }

    /**
     * Returns a JSON response.
     *
     * @param mixed $value
     * @param HttpResponseCode $responseCode
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function json(mixed $value, HttpResponseCode $responseCode = HttpResponseCode::OK): JsonResponse
    {
        $this->responseCode($responseCode);

        return new JsonResponse($this->router, $value);
    }

    /**
     * Returns a no content response effect.
     *
     * @return Effect
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.6
     */
    protected final function noContent(): Effect
    {
        $this->responseCode(HttpResponseCode::NO_CONTENT);

        return new VoidEffect($this->router);
    }

    /**
     * Returns a redirect effect.
     *
     * @param string $destination
     * @param HttpResponseCode $responseCode
     *
     * @return RedirectEffect
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function redirect(string $destination, HttpResponseCode $responseCode = HttpResponseCode::TEMPORARY_REDIRECT): RedirectEffect
    {
        return new RedirectEffect($this->router, $destination, $responseCode);
    }

    /**
     * Sets the response code.
     *
     * @param HttpResponseCode $responseCode
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.6
     */
    protected final function responseCode(HttpResponseCode $responseCode): static
    {
        $this->router
            ->getResponseRegistry()
            ->responseCode($responseCode);

        return $this;
    }

    /**
     * Returns a XML response.
     *
     * @param mixed $value
     * @param HttpResponseCode $responseCode
     *
     * @return XmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function xml(mixed $value, HttpResponseCode $responseCode = HttpResponseCode::OK): XmlResponse
    {
        $this->responseCode($responseCode);

        return new XmlResponse($this->router, $value);
    }

}
