<?php
declare(strict_types=1);

namespace Raxos\Router;

use Closure;
use Raxos\Contract\Router\FrameInterface;
use Raxos\Contract\Router\RouterInterface;
use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Http\Response\NotFoundHttpResponse;
use Raxos\Router\Error\ControllerNotInstantiatedException;
use Raxos\Router\Error\UnexpectedException;
use Raxos\Router\Frame\ClosureFrame;
use Raxos\Router\Frame\FrameStack;
use Raxos\Router\Frame\RouteFrame;
use Throwable;
use function count;
use function implode;

/**
 * Class Runner
 *
 * Executes mapped controller actions through their configured middleware and injected context.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 */
final class Runner
{
    /**
     * Reuses controller instances during this runner's lifetime.
     *
     * @var array<string, object>
     */
    public private(set) array $controllers = [];

    /**
     * Runner constructor.
     *
     * @param RouterInterface $router
     * @param FrameStack $stack
     * @param string[]|null $preflightMethods
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public readonly RouterInterface $router,
        public readonly FrameStack $stack,
        private readonly ?array $preflightMethods = null
    ) {}

    /**
     * Runs the request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function run(HttpRequest $request): HttpResponse
    {
        try {
            $this->router->globals->set('request', $request);

            return $this->closure($this->stack->frames)($request);
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
    public function singleton(
        string $controller,
        ?callable $setup = null
    ): mixed
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
     * @return Closure(HttpRequest):HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private function closure(array $frames): Closure
    {
        $next = static fn(HttpRequest $request) => new NotFoundHttpResponse();

        for ($i = count($frames) - 1; $i >= 0; $i--) {
            $frame = $frames[$i];
            $next = function (HttpRequest $request) use ($frame, $next): HttpResponse {
                $this->router->globals->set('frame', $frame);

                if ($this->preflightMethods !== null && ($frame instanceof ClosureFrame || $frame instanceof RouteFrame)) {
                    return new NoContentHttpResponse()->header('Allow', implode(', ', $this->preflightMethods));
                }

                return $frame->handle($this, $request, $next);
            };
        }

        return $next;
    }
}
