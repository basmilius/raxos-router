<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class MissingInjectionException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class MissingInjectionException extends Exception implements RuntimeExceptionInterface
{

    /**
     * MissingInjectionException constructor.
     *
     * @param string $class
     * @param string|null $method
     * @param string $name
     * @param string $expectedType
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $method,
        public readonly string $name,
        public readonly string $expectedType
    )
    {
        $message = $this->method !== null
            ? "Could not inject parameter {$this->name} into {$this->class}->{$this->method}(), because it was missing. Expected one with type {$this->expectedType}."
            : "Could not inject parameter {$this->name} into {$this->class}, because it was missing. Expected one with type {$this->expectedType}.";

        parent::__construct(
            'router_missing_injection',
            $message
        );
    }

}
