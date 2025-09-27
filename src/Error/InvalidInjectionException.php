<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidInjectionException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class InvalidInjectionException extends Exception implements RuntimeExceptionInterface
{

    /**
     * InvalidInjectionException constructor.
     *
     * @param string $class
     * @param string|null $method
     * @param string $name
     * @param string $actualType
     * @param string $expectedType
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $method,
        public readonly string $name,
        public readonly string $actualType,
        public readonly string $expectedType
    )
    {
        $message = $this->method !== null
            ? "Could not inject parameter {$this->name} into {$this->class}->{$this->method}(). Wrong type {$this->actualType}, expected {$this->expectedType}."
            : "Could nog inject parameter {$this->name} into {$this->class}. Wrong type {$this->actualType}, expected {$this->expectedType}.";

        parent::__construct(
            'router_invalid_injection',
            $message
        );
    }

}
