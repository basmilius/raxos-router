<?php
declare(strict_types=1);

namespace Raxos\Router\Route;

use Exception;
use Raxos\Router\{Router, RouterUtil};
use Raxos\Router\Controller\ExceptionAwareInterface;
use Raxos\Router\Effect\{Effect, NotFoundEffect, VoidEffect};
use Raxos\Router\Error\{RouterException, RuntimeException};
use Raxos\Router\Middleware\Middleware;
use Raxos\Router\Response\Response;
use function array_slice;
use function count;
use function sprintf;

/**
 * Class RouteFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Route
 * @since 1.0.0
 */
readonly class RouteFrame
{

    public string $class;
    public string $method;
    public array $middlewares;
    public array $params;
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
    public function __construct(array $frame, public bool $isFirst, public bool $isLast)
    {
        $this->class = $frame['class'];
        $this->method = $frame['method'];
        $this->middlewares = $frame['middlewares'] ?? [];
        $this->params = $frame['params'] ?? [];
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
    public final function isVersionSatisfiable(float $version): bool
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
            $router->controllers->load($router, $this->class);
        }

        $controller = $router->controllers->get($this->class);
        $params = RouterUtil::prepareParameters($router, $this->params, $this->class, $this->method);

        try {
            if ($this->type[0] === 'void' && $this->isLast) {
                $controller->invoke($this->method, ...$params);

                return new VoidEffect($router);
            }

            return $controller->invoke($this->method, ...$params);
        } catch (Exception $err) {
            if ($controller instanceof ExceptionAwareInterface) {
                return $controller->onException($err);
            }

            throw new RuntimeException(sprintf('Controller method "%s::%s()" threw an exception.', $this->class, $this->method), RuntimeException::ERR_EXCEPTION_IN_HANDLER, $err);
        }
    }

    /**
     * @param Router $router
     * @param string $class
     * @param array $arguments
     *
     * @return Effect|Response|bool|null
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function invokeMiddleware(Router $router, string $class, array $arguments): Effect|Response|bool|null
    {
        try {
            $params = RouterUtil::prepareParametersForClass($class);
            $params = array_slice($params, count($arguments) + 1);
            $params = RouterUtil::prepareParameters($router, $params, $class);

            /** @var Middleware $middleware */
            $middleware = new $class($router, ...$arguments, ...$params);
            $middleware->setFrame($this);

            return $middleware->handle();
        } catch (RouterException $err) {
            throw $err;
        } catch (Exception $err) {
            throw new RuntimeException(sprintf('Middleware "%s" threw an exception.', $class), RuntimeException::ERR_EXCEPTION_IN_MIDDLEWARE, $err);
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

        foreach ($this->middlewares as [$class, $arguments]) {
            $result = $this->invokeMiddleware($router, $class, $arguments);

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
