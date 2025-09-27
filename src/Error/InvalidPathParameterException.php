<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\MappingExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidPathParameterException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class InvalidPathParameterException extends Exception implements MappingExceptionInterface
{

    /**
     * InvalidPathParameterException constructor.
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
            'router_mapping_invalid_path_parameter',
            "Cannot determine regex for parameter {$this->name}."
        );
    }

}
