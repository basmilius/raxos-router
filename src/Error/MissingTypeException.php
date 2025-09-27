<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\MappingExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class MissingTypeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class MissingTypeException extends Exception implements MappingExceptionInterface
{

    /**
     * MissingTypeException constructor.
     *
     * @param string $class
     * @param string $name
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $class,
        public readonly string $name
    )
    {
        parent::__construct(
            'router_mapping_missing_type',
            "Parameter {$this->name} of controller {$this->class} needs a type definition."
        );
    }

}
