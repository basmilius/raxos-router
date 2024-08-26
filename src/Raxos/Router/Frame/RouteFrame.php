<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Router\Definition\Route;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{Response, ResultResponse};
use Raxos\Router\Injector;
use Raxos\Router\Runner;
use function array_column;
use function array_map;
use function implode;
use function iterator_to_array;

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
    public function handle(Runner $runner, Request $request, Closure $next): Response
    {
        $controller = $runner->singleton($this->route->class);

        $parameters = Injector::getValues($runner, $request, $this->route->parameters, $this->route->class, $this->route->method);
        $parameters = iterator_to_array($parameters);

        $response = $controller->{$this->route->method}(...$parameters);

        if ($response instanceof Response) {
            return $response;
        }

        return new ResultResponse($response);
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
