<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class EmptyResultResponseException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 2.0.0
 */
final class EmptyResultResponseException extends Exception implements RuntimeExceptionInterface
{

    /**
     * EmptyResultResponseException constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct()
    {
        parent::__construct(
            'router_empty_result_response',
            'A result-response cannot be used directly.'
        );
    }

}
