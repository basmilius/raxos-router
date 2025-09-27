<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class MissingInstanceException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class MissingInstanceException extends Exception implements RuntimeExceptionInterface
{

    /**
     * MissingInstanceException constructor.
     *
     * @param string $name
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $name
    )
    {
        parent::__construct(
            'router_missing_instance',
            "Instance with name {$this->name} was missing."
        );
    }

}
