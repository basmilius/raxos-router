<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\MappingExceptionInterface;
use Raxos\Error\Exception;
use Raxos\Router\Response\Response;

/**
 * Class InvalidReturnTypeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class InvalidReturnTypeException extends Exception implements MappingExceptionInterface
{

    /**
     * InvalidReturnTypeException constructor.
     *
     * @param string $class
     * @param string $method
     * @param string $expectedType
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $class,
        public readonly string $method,
        public readonly string $expectedType = Response::class
    )
    {
        parent::__construct(
            'router_mapping_invalid_return_type',
            "Controller method {$this->class}->{$this->method}() has an invalid return type. It should return {$this->expectedType}."
        );
    }

}
