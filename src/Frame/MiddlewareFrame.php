<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Exception;
use Raxos\Contract\Router\{FrameInterface, MiddlewareInterface};
use Raxos\Router\{Injector, Runner};
use Raxos\Router\Definition\Middleware;
use Raxos\Router\Error\UnexpectedException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use function array_column;
use function array_map;
use function implode;

/**
 * Class MiddlewareFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.1.0
 */
final readonly class MiddlewareFrame implements FrameInterface
{

    /**
     * MiddlewareFrame constructor.
     *
     * @param Middleware $middleware
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public Middleware $middleware
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function handle(Runner $runner, Request $request, Closure $next): Response
    {
        try {
            /** @var MiddlewareInterface $instance */
            $instance = new $this->middleware->class(...$this->middleware->arguments);
            $injectables = Injector::getValues($runner, $request, $this->middleware->injectables, $this->middleware->class);

            Injector::injectClassProperties($instance, $injectables);

            return $instance->handle($request, $next);
        } catch (Exception $err) {
            throw new UnexpectedException($err, (string)$this);
        }
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __toString(): string
    {
        $injectables = implode(', ', array_map(static fn(string $name) => "\${$name}", array_column($this->middleware->injectables, 'name')));

        return "{$this->middleware->class} { {$injectables} }";
    }

}
