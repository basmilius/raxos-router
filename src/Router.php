<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Foundation\Collection\Map;
use Raxos\Router\Contract\RouterInterface;
use Raxos\Router\Error\MappingException;
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
     * @param array<string, array<string, FrameStack>> $dynamicRoutes
     * @param array<string, array<string, FrameStack>> $staticRoutes
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
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
     * @param class-string[] $controllers
     *
     * @return self
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function createFromControllers(array $controllers): self
    {
        return new self(...Mapper::for($controllers));
    }

    /**
     * Returns a router with the given mapping.
     *
     * @param array<string, array<string, FrameStack>> $dynamicRoutes
     * @param array<string, array<string, FrameStack>> $staticRoutes
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function createFromMapping(array $dynamicRoutes, array $staticRoutes): self
    {
        return new self($dynamicRoutes, $staticRoutes);
    }

}
