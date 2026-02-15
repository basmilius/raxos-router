<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Collection\Map;
use Raxos\Contract\Container\ContainerInterface;
use Raxos\Contract\Router\{MappingExceptionInterface, RouterInterface};
use Raxos\Router\Frame\FrameStack;

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 */
readonly class Router implements RouterInterface
{

    use Resolvable;

    public Map $globals;

    /**
     * Router constructor.
     *
     * @param ContainerInterface|null $container
     * @param array<string, array<string, FrameStack>> $dynamicRoutes
     * @param array<string, array<string, FrameStack>> $staticRoutes
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public ?ContainerInterface $container,
        public array $dynamicRoutes = [],
        public array $staticRoutes = []
    )
    {
        $this->globals = new Map();
        $this->globals->set('router', $this);
    }

    /**
     * Creates a router with the given controllers.
     *
     * @param ContainerInterface|null $container
     * @param class-string[] $controllers
     *
     * @return self
     * @throws MappingExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function createFromControllers(?ContainerInterface $container, array $controllers): self
    {
        return new self($container, ...Mapper::for($controllers));
    }

    /**
     * Returns a router with the given mapping.
     *
     * @param ContainerInterface|null $container
     * @param array<string, array<string, FrameStack>> $dynamicRoutes
     * @param array<string, array<string, FrameStack>> $staticRoutes
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function createFromMapping(?ContainerInterface $container, array $dynamicRoutes, array $staticRoutes): self
    {
        return new self($container, $dynamicRoutes, $staticRoutes);
    }

}
