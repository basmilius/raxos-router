<?php
declare(strict_types=1);

namespace Raxos\Router\Effect;

use Raxos\Router\Router;

/**
 * Class Effect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Effect
 * @since 1.0.0
 */
abstract class Effect
{

    /**
     * Effect constructor.
     *
     * @param Router $router
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(protected Router $router)
    {
    }

    /**
     * Gets the router instance.
     *
     * @return Router
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getRouter(): Router
    {
        return $this->router;
    }

}
