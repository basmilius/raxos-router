<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpCode;
use Raxos\Router\Effect\RedirectEffect;
use function array_key_exists;
use function is_array;

/**
 * Trait ResponseMethods
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Controller
 * @since 1.0.0
 */
trait ResponseMethods
{

    private static array $headers = [];

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
        if (array_key_exists($name, static::$headers) && !$replace) {
            if (is_array(static::$headers[$name])) {
                static::$headers[$name][] = $value;
            } else {
                static::$headers[$name] = [static::$headers[$name], $value];
            }
        } else {
            static::$headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Returns a JSON response.
     *
     * @param mixed $value
     *
     * @return JsonResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    protected final function json(mixed $value): JsonResponse
    {
        return new JsonResponse($this->router, [], $value);
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
    protected final function redirect(string $destination, #[ExpectedValues(flagsFromClass: HttpCode::class)] int $responseCode = HttpCode::TEMPORARY_REDIRECT): RedirectEffect
    {
        return new RedirectEffect($this->router, $destination, $responseCode);
    }

}
