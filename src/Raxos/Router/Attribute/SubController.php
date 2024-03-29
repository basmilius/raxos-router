<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Router\Controller\Controller;
use Raxos\Router\Error\RegisterException;

/**
 * Class SubController
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
readonly class SubController
{

    /**
     * SubController constructor.
     *
     * @param string $class
     *
     * @throws RegisterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(public string $class)
    {
        if (!is_subclass_of($class, Controller::class)) {
            throw new RegisterException(sprintf('Controller class must extend %s.', Controller::class), RegisterException::ERR_NOT_A_CONTROLLER);
        }
    }

}
