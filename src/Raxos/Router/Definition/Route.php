<?php
declare(strict_types=1);

namespace Raxos\Router\Definition;

use Raxos\Foundation\Contract\SerializableInterface;
use Raxos\Router\Attribute\AbstractRoute;

/**
 * Class Route
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Definition
 * @since 1.1.0
 */
final readonly class Route implements SerializableInterface
{

    /**
     * Route constructor.
     *
     * @param string $class
     * @param string $method
     * @param AbstractRoute[] $routes
     * @param Middleware[] $middlewares
     * @param Injectable[] $parameters
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $class,
        public string $method,
        public array $routes,
        public array $middlewares,
        public array $parameters
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __serialize(): array
    {
        return [
            $this->class,
            $this->method,
            $this->routes,
            $this->middlewares,
            $this->parameters
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
            $this->class,
            $this->method,
            $this->routes,
            $this->middlewares,
            $this->parameters
        ] = $data;
    }

}
