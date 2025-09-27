<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\MappingExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class TypeTooComplexException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class TypeTooComplexException extends Exception implements MappingExceptionInterface
{

    /**
     * TypeTooComplexException constructor.
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
            'router_mapping_type_too_complex',
            "Parameter {$this->name} has a type that is too complex."
        );
    }

}
