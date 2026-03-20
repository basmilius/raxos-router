<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;
use Throwable;

/**
 * Class ValidationFailedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class ValidationFailedException extends Exception implements RuntimeExceptionInterface
{

    /**
     * ValidationFailedException constructor.
     *
     * @param Throwable $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly Throwable $err
    )
    {
        parent::__construct(
            'router_validation_failed',
            'Request validation failed.',
            previous: $this->err
        );
    }

}
