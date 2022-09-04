<?php
declare(strict_types=1);

namespace Raxos\Router\Middleware;

use Raxos\Http\Validate\Error\ValidatorException;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;
use Raxos\Router\Route\RouteFrame;

/**
 * Interface MiddlewareInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Middleware
 * @since 1.0.2
 */
interface MiddlewareInterface
{

    /**
     * Gets the route frame.
     *
     * @return RouteFrame|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.2
     */
    public function getFrame(): ?RouteFrame;

    /**
     * Handles the request.
     *
     * @return Effect|Response|bool|null
     * @throws ValidatorException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.2
     */
    public function handle(): Effect|Response|bool|null;

}
