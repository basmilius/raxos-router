<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Reflection\ReflectionFailedExceptionInterface;
use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;
use ReflectionException;

/**
 * Class ReflectionErrorException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class ReflectionErrorException extends Exception implements RuntimeExceptionInterface, ReflectionFailedExceptionInterface
{

    /**
     * ReflectionErrorException constructor.
     *
     * @param ReflectionException $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly ReflectionException $err
    )
    {
        parent::__construct(
            'router_reflection_error',
            'Reflection error.',
            previous: $err
        );
    }

}
