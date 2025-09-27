<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class MissingFileException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class MissingFileException extends Exception implements RuntimeExceptionInterface
{

    /**
     * MissingFileException constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $path
    )
    {
        parent::__construct(
            'router_missing_file',
            "File {$this->path} was not found."
        );
    }

}
