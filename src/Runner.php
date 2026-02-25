<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Contract\Router\{FrameInterface, RouterInterface, RuntimeExceptionInterface};
use Raxos\Router\Error\{ControllerNotInstantiatedException, UnexpectedException};
use Raxos\Router\Frame\FrameStack;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{NotFoundResponse, Response};
use Throwable;
use function count;

/**
 * Class Runner
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 */
final class Runner
{

    /** @var array<string, object> */
    public private(set) array $controllers = [];

    private int $dispatchIndex = 0;

    /**
     * Runner constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public readonly RouterInterface $router,
        public readonly FrameStack $stack
    ) {}

    /**
     * Runs the request.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function run(Request $request): Response
    {
        try {
            $this->router->globals->set('request', $request);
            $this->dispatchIndex = 0;

            return $this->dispatch($request);
        } catch (Throwable $err) {
            if ($err instanceof RuntimeExceptionInterface) {
                throw $err;
            }

            /** @var FrameInterface $frame */
            $frame = $this->router->globals->get('frame');

            throw new UnexpectedException($err, (string)$frame);
        }
    }

    /**
     * Returns a controller instance.
     *
     * @template TController
     *
     * @param class-string<TController> $controller
     * @param callable():TController|null $setup
     *
     * @return TController|null
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function singleton(string $controller, ?callable $setup = null): mixed
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        }

        if ($setup === null) {
            throw new ControllerNotInstantiatedException($controller);
        }

        return $this->controllers[$controller] ??= $setup();
    }

    /**
     * Dispatches the request to the next frame in the pipeline.
     *
     * @param Request $request
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private function dispatch(Request $request): Response
    {
        $frames = $this->stack->frames;

        if ($this->dispatchIndex >= count($frames)) {
            return new NotFoundResponse();
        }

        $frame = $frames[$this->dispatchIndex++];
        $this->router->globals->set('frame', $frame);

        return $frame->handle($this, $request, $this->dispatch(...));
    }

}
