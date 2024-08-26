<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Effect;

use Raxos\OldRouter\Response\Response;
use Raxos\OldRouter\Router;

/**
 * Class ResponseEffect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Effect
 * @since 1.0.0
 */
final readonly class ResponseEffect extends Effect
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
    public function __construct(
        Router $router,
        public Response $response
    )
    {
        parent::__construct($router);
    }

}
