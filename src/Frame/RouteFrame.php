<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Contract\Router\FrameInterface;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\Response\ResultHttpResponse;
use Raxos\Router\{Injector, Runner};
use Raxos\Router\Definition\Route;
use function array_column;
use function array_map;
use function implode;

/**
 * Class RouteFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.1.0
 */
final readonly class RouteFrame implements FrameInterface
{

    /**
     * RouteFrame constructor.
     *
     * @param Route $route
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public Route $route
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function handle(Runner $runner, HttpRequest $request, Closure $next): HttpResponse
    {
        $controller = $runner->singleton($this->route->class);
        $parameters = Injector::getValues($runner, $request, $this->route->parameters, $this->route->class, $this->route->method);

        $response = $controller->{$this->route->method}(...$parameters);

        if ($response instanceof HttpResponse) {
            return $response;
        }

        return new ResultHttpResponse($response);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __toString(): string
    {
        $parameters = implode(', ', array_map(static fn(string $name) => "\${$name}", array_column($this->route->parameters, 'name')));

        return "{$this->route->class}->{$this->route->method}({$parameters})";
    }

}
