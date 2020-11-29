<?php
declare(strict_types=1);

namespace Raxos\Router\Route;

use Exception;
use Raxos\Router\Controller\ExceptionAwareInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Effect\NotFoundEffect;
use Raxos\Router\Effect\VoidEffect;
use Raxos\Router\Error\RouterException;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Middleware\Middleware;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use Raxos\Router\RouterUtil;
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
class RouteFrame
{

    private string $class;
    private string $method;
    private array $middlewares;
    private array $params;
    private array $request;
    private string $type;

    /**
     * RouteFrame constructor.
     *
     * @param array $frame
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(array $frame)
    {
        $this->class = $frame['class'];
        $this->method = $frame['method'];
        $this->middlewares = $frame['middlewares'] ?? [];
        $this->params = $frame['params'] ?? [];
        $this->request = $frame['request'];
        $this->type = $frame['type'];
    }

    /**
     * Invokes the controller method.
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
        $controllers = $router->getControllers();

        foreach ($params as $name => $value) {
            $router->parameter($name, $value);
        }

        $result = $this->invokeMiddlewares($router, $returnResult);

        if ($returnResult) {
            return $result;
        }

        if (!$controllers->has($this->class)) {
            $controllers->load($router, $this->class);
        }

        $controller = $controllers->get($this->class);
        $params = RouterUtil::prepareParameters($router, $this->params, $this->class, $this->method);

        try {
            if ($this->type === 'void') {
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
    private function invokeMiddleware(Router $router, string $class, array $arguments): Effect|Response|bool|null
    {
        try {
            $params = RouterUtil::prepareParametersForClass($class);
            $params = array_slice($params, count($arguments) + 1);
            $params = RouterUtil::prepareParameters($router, $params, $class);

            /** @var Middleware $middleware */
            $middleware = new $class($router, ...$arguments, ...$params);

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
    private function invokeMiddlewares(Router $router, ?bool &$returnResult = null): Effect|Response|null
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
