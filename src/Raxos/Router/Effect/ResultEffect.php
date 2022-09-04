<?php
declare(strict_types=1);

namespace Raxos\Router\Effect;

use Raxos\Router\Router;

/**
 * Class ResultEffect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Effect
 * @since 1.0.0
 */
final class ResultEffect extends Effect
{

    /**
     * ResultEffect constructor.
     *
     * @param Router $router
     * @param mixed $result
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(Router $router, public readonly mixed $result)
    {
        parent::__construct($router);
    }

}
