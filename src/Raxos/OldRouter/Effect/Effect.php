<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Effect;

use Raxos\OldRouter\Router;

/**
 * Class Effect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Effect
 * @since 1.0.0
 */
abstract readonly class Effect
{

    /**
     * Effect constructor.
     *
     * @param Router $router
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(public Router $router) {}

}
