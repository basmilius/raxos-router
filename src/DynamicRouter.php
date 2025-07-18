<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Foundation\Collection\Map;
use Raxos\Http\HttpMethod;
use Raxos\Router\Contract\RouterInterface;
use Raxos\Router\Error\MappingException;
use Raxos\Router\Frame\{ClosureFrame, FrameStack, MiddlewareFrame};
use ReflectionException;
use ReflectionFunction;
use function count;
use function iterator_to_array;

/**
 * Class DynamicRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.5.0
 */
class DynamicRouter implements RouterInterface
{

    use Resolvable;

    public private(set) Map $globals;
    public private(set) array $dynamicRoutes = [];
    public private(set) array $staticRoutes = [];

    /**
     * DynamicRouter constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function __construct()
    {
        $this->globals = new Map();
        $this->globals->set('router', $this);
    }

    /**
     * Registers a GET route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function get(string $path, callable $handler): void
    {
        $this->route(HttpMethod::GET, $path, $handler);
    }

    /**
     * Registers a POST route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function post(string $path, callable $handler): void
    {
        $this->route(HttpMethod::POST, $path, $handler);
    }

    /**
     * Registers a PUT route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function put(string $path, callable $handler): void
    {
        $this->route(HttpMethod::PUT, $path, $handler);
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function delete(string $path, callable $handler): void
    {
        $this->route(HttpMethod::DELETE, $path, $handler);
    }

    /**
     * Registers a PATCH route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function patch(string $path, callable $handler): void
    {
        $this->route(HttpMethod::PATCH, $path, $handler);
    }

    /**
     * Registers an OPTIONS route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function options(string $path, callable $handler): void
    {
        $this->route(HttpMethod::OPTIONS, $path, $handler);
    }

    /**
     * Registers a HEAD route.
     *
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     * @see self::route()
     */
    public function head(string $path, callable $handler): void
    {
        $this->route(HttpMethod::HEAD, $path, $handler);
    }

    /**
     * Adds a route.
     *
     * @param HttpMethod $method
     * @param string $path
     * @param callable $handler
     *
     * @return void
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function route(HttpMethod $method, string $path, callable $handler): void
    {
        try {
            $reflector = new ReflectionFunction($handler);

            $parameters = iterator_to_array(Mapper::injectablesForMethod($reflector));

            $path = RouterUtil::normalizePath($path);
            $pathPlain = $path;

            if (!empty($parameters)) {
                $path = RouterUtil::convertPath($path, $parameters);
            }

            $middlewares = [];

            foreach (Mapper::middlewares($reflector) as $middleware) {
                $middlewares[] = new MiddlewareFrame($middleware);
            }

            $stack = new FrameStack($method, $path, $pathPlain, [
                ...$middlewares,
                new ClosureFrame($reflector->getClosure(), $parameters)
            ]);

            if (empty($parameters)) {
                $this->staticRoutes[$path][$method->value] = $stack;
            } else {
                $segments = RouterUtil::pathToSegments($path);
                $segmentCount = count($segments);

                $this->dynamicRoutes[$segmentCount][$path]['segments'] ??= $segments;
                $this->dynamicRoutes[$segmentCount][$path][$method->value] = $stack;
            }
        } catch (ReflectionException $err) {
            throw MappingException::reflectionError($err);
        }
    }

}
