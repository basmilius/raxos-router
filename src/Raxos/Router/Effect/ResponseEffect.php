<?php
declare(strict_types=1);

namespace Raxos\Router\Effect;

use Raxos\Router\Response\Response;
use Raxos\Router\Router;

/**
 * Class ResponseEffect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Effect
 * @since 1.0.0
 */
final class ResponseEffect extends Effect
{

    /**
     * ResponseEffect constructor.
     *
     * @param Router $router
     * @param Response $response
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(Router $router, public readonly Response $response)
    {
        parent::__construct($router);
    }

}
