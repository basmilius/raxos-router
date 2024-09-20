<?php
declare(strict_types=1);

namespace Raxos\Router;

use Closure;
use Exception;
use Raxos\Http\HttpRequest;
use Raxos\Router\Contract\FrameInterface;
use Raxos\Router\Error\{RouterException, RuntimeException};
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
    public array $controllers = [];

    /**
     * Runner constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public readonly Router $router,
        public readonly FrameStack $stack
    ) {}

    /**
     * Runs the request.
     *
     * @param Request $request
     *
     * @return Response
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function run(Request $request): Response
    {
        $frames = $this->stack->frames;

        try {
            $this->router->globals->set('request', $request);

            return $this->closure($frames)($request);
        } catch (Exception $err) {
            if ($err instanceof RouterException) {
                throw $err;
            }

            /** @var FrameInterface $frame */
            $frame = $this->router->globals->get('frame');

            throw RuntimeException::unexpected($err, (string)$frame);
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
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function singleton(string $controller, ?callable $setup = null): mixed
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        }

        if ($setup === null) {
            throw RuntimeException::controllerNotInstantiated($controller);
        }

        return $this->controllers[$controller] ??= $setup();
    }

    /**
     * Creates a callable frame stack.
     *
     * @param FrameInterface[] $frames
     *
     * @return Closure(HttpRequest):Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private function closure(array $frames): Closure
    {
        $frame = array_shift($frames);

        if ($frame === null) {
            return static fn() => new NotFoundResponse();
        }

        return function (Request $request) use ($frame, $frames): Response {
            $this->router->globals->set('frame', $frame);

            return $frame->handle($this, $request, $this->closure($frames));
        };
    }

}
