<?php
declare(strict_types=1);

namespace Raxos\Router;

use Closure;
use Exception;
use Raxos\Contract\Router\{FrameInterface, RouterInterface, RuntimeExceptionInterface};
use Raxos\Router\Error\{ControllerNotInstantiatedException, UnexpectedException};
use Raxos\Router\Frame\FrameStack;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{NotFoundResponse, Response};
use function array_shift;

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

            return $this->closure($this->stack->frames)($request);
        } catch (Exception $err) {
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
     * Creates a callable frame stack.
     *
     * @param FrameInterface[] $frames
     *
     * @return Closure(Request):Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private function closure(array $frames): Closure
    {
        if (empty($frames)) {
            return static fn() => new NotFoundResponse();
        }

        $frame = array_shift($frames);

        return function (Request $request) use ($frame, $frames): Response {
            $this->router->globals->set('frame', $frame);

            return $frame->handle($this, $request, $this->closure($frames));
        };
    }

}
