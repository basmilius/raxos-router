<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Foundation\Error\RaxosException;
use Throwable;

/**
 * Class RouterException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 1.0.0
 */
abstract class RouterException extends RaxosException
{

    /**
     * RouterException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
