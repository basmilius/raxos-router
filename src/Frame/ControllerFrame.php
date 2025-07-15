<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Router\{Injector, Runner};
use Raxos\Router\Contract\FrameInterface;
use Raxos\Router\Definition\ControllerClass;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use function array_column;
use function array_map;
use function implode;

/**
 * Class ControllerFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.1.0
 */
final readonly class ControllerFrame implements FrameInterface
{

    /**
     * ControllerFrame constructor.
     *
     * @param ControllerClass $controller
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public ControllerClass $controller
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function handle(Runner $runner, Request $request, Closure $next): Response
    {
        $runner->singleton($this->controller->class, fn() => $this->setup($runner, $request));

        return $next($request);
    }

    /**
     * Sets up the controller if needed.
     *
     * @return mixed
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private function setup(Runner $runner, Request $request): object
    {
        $parameters = Injector::getValues($runner, $request, $this->controller->parameters, $this->controller->class);

        $instance = new $this->controller->class(...$parameters);
        $injectables = Injector::getValues($runner, $request, $this->controller->injectables, $this->controller->class);

        Injector::injectClassProperties($instance, $injectables);

        return $instance;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __toString(): string
    {
        $injectables = implode(', ', array_map(static fn(string $name) => "\${$name}", array_column($this->controller->injectables, 'name')));
        $parameters = implode(', ', array_map(static fn(string $name) => "\${$name}", array_column($this->controller->parameters, 'name')));

        return "{$this->controller->class}({$parameters}) { {$injectables} }";
    }

}
