<?php
declare(strict_types=1);

namespace Raxos\Router\Middleware;

use Raxos\Router\Response\ResponseMethods;
use Raxos\Router\Route\RouteFrame;
use Raxos\Router\Router;

/**
 * Class Middleware
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Middleware
 * @since 1.0.0
 */
abstract class Middleware implements MiddlewareInterface
{

    use ResponseMethods;

    private ?RouteFrame $frame = null;

    /**
     * Middleware constructor.
     *
     * @param Router $router
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(public readonly Router $router)
    {
    }

    /**
     * Gets the router frame.
     *
     * @return RouteFrame|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getFrame(): ?RouteFrame
    {
        return $this->frame;
    }

    /**
     * Sets the router frame.
     *
     * @param RouteFrame $frame
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     * @internal
     */
    public final function setFrame(RouteFrame $frame): void
    {
        $this->frame = $frame;
    }

    /**
     * Adds a parameter.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected final function parameter(string $name, mixed $value): static
    {
        $this->router->parameter($name, $value);

        return $this;
    }

}
