<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use Raxos\Router\Runner;
use Stringable;

/**
 * Interface FrameInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.1.0
 */
interface FrameInterface extends Stringable
{

    /**
     * Executes the route frame and returns the response for the given request.
     *
     * @param Runner $runner
     * @param Request $request
     * @param Closure(Request):Response $next
     *
     * @return Response
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function handle(Runner $runner, Request $request, Closure $next): Response;

}
