<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class ControllerNotInstantiatedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class ControllerNotInstantiatedException extends Exception implements RuntimeExceptionInterface
{

    /**
     * ControllerNotInstantiatedException constructor.
     *
     * @param string $controllerClass
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $controllerClass
    )
    {
        parent::__construct(
            'router_controller_not_instantiated',
            "Controller {$this->controllerClass} is not instantiated."
        );
    }

}
