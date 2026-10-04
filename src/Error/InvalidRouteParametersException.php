<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidRouteParametersException
 *
 * Reports reverse-route parameters that cannot satisfy the selected route template.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 3.3.0
 */
final class InvalidRouteParametersException extends Exception implements RuntimeExceptionInterface
{
    /**
     * Identifies an invalid route selection or parameter without constructing a partial URL.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function __construct(string $message)
    {
        parent::__construct('router_invalid_route_parameters', $message);
    }
}
