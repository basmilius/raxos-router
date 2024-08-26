<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Effect;

use Raxos\OldRouter\Router;

/**
 * Class ResultEffect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Effect
 * @since 1.0.0
 */
final readonly class ResultEffect extends Effect
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
    public function __construct(
        Router $router,
        public mixed $result
    )
    {
        parent::__construct($router);
    }

}
