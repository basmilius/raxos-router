<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidHandlerException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class InvalidHandlerException extends Exception implements RuntimeExceptionInterface
{

    /**
     * InvalidHandlerException constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct()
    {
        parent::__construct(
            'router_invalid_handler',
            'Route handler cannot be found or is invalid.'
        );
    }

}
