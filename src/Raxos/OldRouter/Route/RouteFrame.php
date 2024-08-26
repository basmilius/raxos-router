<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Route;

use Exception;
use JetBrains\PhpStorm\Pure;
use Raxos\OldRouter\{MiddlewareInterface, Router, RouterUtil};
use Raxos\OldRouter\Controller\ExceptionAwareInterface;
use Raxos\OldRouter\Effect\{Effect, NotFoundEffect, VoidEffect};
use Raxos\OldRouter\Error\{RouterException, RuntimeException};
use Raxos\OldRouter\Response\Response;
use function array_slice;

/**
 * Class RouteFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Route
 * @since 1.0.0
 */
final readonly class RouteFrame
{

    public string $class;
    public string $method;
    public array $middlewares;
    public array $params;
    public array $properties;
    public array $request;
    public array $type;
    public ?array $version;

    /**
     * RouteFrame constructor.
     *
     * @param array $frame
     * @param bool $isFirst
     * @param bool $isLast
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        array $frame,
        public bool $isFirst,
        public bool $isLast
    )
    {
        $this->class = $frame['class'];
        $this->method = $frame['method'];
        $this->middlewares = $frame['middlewares'] ?? [];
        $this->params = $frame['params'] ?? [];
        $this->properties = $frame['properties'] ?? [];
        $this->request = $frame['request'];
        $this->type = $frame['type'];
        $this->version = $frame['version'] ?? null;
    }

    /**
     * Returns TRUE if the given version is satisfiable.
     *
     * @param float $version
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    public function isVersionSatisfiable(float $version): bool
    {
        if ($this->version === null) {
            return true;
        }

        if ($this->version[0] !== null && $this->version[1] !== null) {
            return $version >= $this->version[0] && $version <= $this->version[1];
        }

        if ($this->version[0] !== null) {
            return $version >= $this->version[0];
        }

        if ($this->version[1] !== null) {
            return $version <= $this->version[1];
        }

        return true;
    }

    /**
     * Invokes the route frame.
     *
     * @param Router $router
     * @param array $params
     *
     * @return mixed
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function invoke(Router $router, array $params): mixed
    {
        foreach ($params as $name => $value) {
            $router->parameter($name, $value);
        }

        $result = $this->invokeMiddlewares($router, $returnResult);

        if ($returnResult) {
            return $result;
        }

        return $this->invokeController($router);
    }

    /**
     * Invokes the controller method.
     *
     * @param Router $router
     *
     * @return mixed
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function invokeController(Router $router): mixed
    {
        if (!$router->controllers->has($this->class)) {
            $router->controllers->load($router, $this->class, $this->properties);
        }

        $controller = $router->controllers->get($this->class);
        $injections = RouterUtil::getInjectionValues($router, $this->params, $this->class, $this->method, $this);

        try {
            if ($this->type[0] === 'void' && $this->isLast) {
                $controller->invoke($this->method, ...$injections);

                return new VoidEffect($router);
            }

            return $controller->invoke($this->method, ...$injections);
        } catch (Exception $err) {
            if ($controller instanceof ExceptionAwareInterface) {
                return $controller->onException($err);
            }

            throw RuntimeException::controllerError($this, $err);
        }
    }

    /**
     * Invokes a single middleware.
     *
     * @param Router $router
     * @param class-string<MiddlewareInterface> $class
     * @param array $arguments
     * @param array $properties
     *
     * @return Effect|Response|bool|null
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function invokeMiddleware(Router $router, string $class, array $arguments, array $properties): Effect|Response|bool|null
    {
        try {
            $injections = RouterUtil::getInjectionsForConstructor($class);
            $injections = array_slice($injections, count($arguments));
            $injections = RouterUtil::getInjectionValues($router, $injections, $class, frame: $this);

            $instance = new $class(...$arguments, ...$injections);

            RouterUtil::injectProperties(
                $instance,
                RouterUtil::getInjectionValues($router, $properties, $class, frame: $this)
            );

            return $instance->handle();
        } catch (RouterException $err) {
            throw $err;
        } catch (Exception $err) {
            throw RuntimeException::middlewareError($class, $err);
        }
    }

    /**
     * Invokes all middlewares for the current route frame.
     *
     * @param Router $router
     * @param bool|null $returnResult
     *
     * @return Effect|Response|null
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function invokeMiddlewares(Router $router, ?bool &$returnResult = null): Effect|Response|null
    {
        $returnResult = false;

        foreach ($this->middlewares as [$class, $arguments, $properties]) {
            $result = $this->invokeMiddleware($router, $class, $arguments, $properties);

            if ($result === true) {
                continue;
            }

            if ($result === false) {
                $returnResult = true;

                return new NotFoundEffect($router);
            }

            if ($result instanceof Effect || $result instanceof Response) {
                $returnResult = true;

                return $result;
            }
        }

        return null;
    }

}
