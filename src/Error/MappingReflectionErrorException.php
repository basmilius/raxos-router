<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Reflection\ReflectionFailedExceptionInterface;
use Raxos\Contract\Router\MappingExceptionInterface;
use Raxos\Error\Exception;
use ReflectionException;

/**
 * Class MappingReflectionErrorException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class MappingReflectionErrorException extends Exception implements MappingExceptionInterface, ReflectionFailedExceptionInterface
{

    /**
     * MappingReflectionErrorException constructor.
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
            'router_mapping_reflection_error',
            'Reflection error.',
            previous: $err
        );
    }

}
