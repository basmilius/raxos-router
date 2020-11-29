<?php
declare(strict_types=1);

namespace Raxos\Router\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use Raxos\Http\HttpCode;
use Raxos\Router\Router;

/**
 * Class RedirectEffect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Effect
 * @since 1.0.0
 */
final class RedirectEffect extends Effect
{

    /**
     * RedirectEffect constructor.
     *
     * @param Router $router
     * @param string $destination
     * @param int $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(Router $router, private string $destination, #[ExpectedValues(flagsFromClass: HttpCode::class)] private int $responseCode = HttpCode::TEMPORARY_REDIRECT)
    {
        parent::__construct($router);
    }

    /**
     * Gets the destination.
     *
     * @return string
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Gets the response code.
     *
     * @return int
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[ExpectedValues(flagsFromClass: HttpCode::class)]
    public final function getResponseCode(): int
    {
        return $this->responseCode;
    }

}
