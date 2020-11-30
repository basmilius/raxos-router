<?php
declare(strict_types=1);

namespace Raxos\Router\Controller;

use Raxos\Router\Error\RouterException;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Router;
use Raxos\Router\RouterUtil;
use function sprintf;

/**
 * Class ControllerContainer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Controller
 * @since 1.0.0
 */
final class ControllerContainer
{

    private array $instances = [];

    /**
     * Gets the given controller instance.
     *
     * @param string $class
     *
     * @return Controller
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function get(string $class): Controller
    {
        return $this->instances[$class] ?? throw new RuntimeException(sprintf('Instance of controller "%s" not found.', $class), RuntimeException::ERR_INSTANCE_NOT_FOUND);
    }

    /**
     * Checks if the given controller is loaded.
     *
     * @param string $class
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function has(string $class): bool
    {
        return isset($this->instances[$class]);
    }

    /**
     * Loads the given controller with the given parameters.
     *
     * @param Router $router
     * @param string $class
     *
     * @return Controller
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function load(Router $router, string $class): Controller
    {
        $params = RouterUtil::prepareParametersForClass($class);
        $params = RouterUtil::prepareParameters($router, $params, $class);

        return $this->instances[$class] = new $class(...$params);
    }

}
