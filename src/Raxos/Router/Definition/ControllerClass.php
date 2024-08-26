<?php
declare(strict_types=1);

namespace Raxos\Router\Definition;

use Raxos\Foundation\Contract\SerializableInterface;

/**
 * Class ControllerClass
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Definition
 * @since 1.1.0
 */
final class ControllerClass implements SerializableInterface
{

    /**
     * ControllerClass constructor.
     *
     * @param string $prefix
     * @param string $class
     * @param self[] $children
     * @param Injectable[] $injectables
     * @param Middleware[] $middlewares
     * @param Injectable[] $parameters
     * @param Route[] $routes
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $prefix,
        public string $class,
        public array $children,
        public array $injectables,
        public array $middlewares,
        public array $parameters,
        public array $routes
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __serialize(): array
    {
        return [
            $this->prefix,
            $this->class,
            $this->children,
            $this->injectables,
            $this->middlewares,
            $this->parameters,
            $this->routes
        ];
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __unserialize(array $data): void
    {
        [
            $this->prefix,
            $this->class,
            $this->children,
            $this->injectables,
            $this->middlewares,
            $this->parameters,
            $this->routes
        ] = $data;
    }

}
