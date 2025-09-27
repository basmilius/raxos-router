<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;
use Throwable;

/**
 * Class UnexpectedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class UnexpectedException extends Exception implements RuntimeExceptionInterface
{

    /**
     * UnexpectedException constructor.
     *
     * @param Throwable $err
     * @param string $call
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly Throwable $err,
        public readonly string $call
    )
    {
        parent::__construct(
            'router_unexpected_error',
            "Something went wrong while running {$this->call}.",
            previous: $this->err
        );
    }

}
