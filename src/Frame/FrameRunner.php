<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Contract\Router\FrameInterface;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use Raxos\Router\Runner;

/**
 * Class FrameRunner
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.6.0
 */
final readonly class FrameRunner
{

    /**
     * FrameRunner constructor.
     *
     * @param Runner $runner
     * @param FrameInterface $frame
     * @param Closure $next
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __construct(
        private Runner $runner,
        private FrameInterface $frame,
        private Closure $next
    ) {}

    /**
     * Invokes the frame with the given request.
     *
     * @param Request $request
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.6.0
     */
    public function __invoke(Request $request): Response
    {
        $this->runner->router->globals->set('frame', $this->frame);

        return $this->frame->handle($this->runner, $request, $this->next);
    }

}
