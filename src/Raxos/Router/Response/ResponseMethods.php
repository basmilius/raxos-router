<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use JetBrains\PhpStorm\ExpectedValues;
use Raxos\Http\HttpCode;
use Raxos\Router\Effect\RedirectEffect;
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
     * @param int $responseCode
     *
     * @return HtmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function html(string $value, #[ExpectedValues(valuesFromClass: HttpCode::class)] int $responseCode = HttpCode::OK): HtmlResponse
    {
        $this->router
            ->getResponseRegistry()
            ->responseCode($responseCode);

        return new HtmlResponse($this->router, $value);
    }

    /**
     * Returns a JSON response.
     *
     * @param mixed $value
     * @param int $responseCode
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function json(mixed $value, #[ExpectedValues(valuesFromClass: HttpCode::class)] int $responseCode = HttpCode::OK): JsonResponse
    {
        $this->router
            ->getResponseRegistry()
            ->responseCode($responseCode);

        return new JsonResponse($this->router, $value);
    }

    /**
     * Returns a redirect effect.
     *
     * @param string $destination
     * @param int $responseCode
     *
     * @return RedirectEffect
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function redirect(string $destination, #[ExpectedValues(valuesFromClass: HttpCode::class)] int $responseCode = HttpCode::TEMPORARY_REDIRECT): RedirectEffect
    {
        return new RedirectEffect($this->router, $destination, $responseCode);
    }

    /**
     * Returns a XML response.
     *
     * @param mixed $value
     * @param int $responseCode
     *
     * @return XmlResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function xml(mixed $value, #[ExpectedValues(valuesFromClass: HttpCode::class)] int $responseCode = HttpCode::OK): XmlResponse
    {
        $this->router
            ->getResponseRegistry()
            ->responseCode($responseCode);

        return new XmlResponse($this->router, $value);
    }

}
