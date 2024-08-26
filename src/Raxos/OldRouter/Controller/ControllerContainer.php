<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Controller;

use Raxos\OldRouter\{Router, RouterUtil};
use Raxos\OldRouter\Error\{RouterException, RuntimeException};
use ReflectionException;
use function sprintf;

/**
 * Class ControllerContainer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Controller
 * @since 1.0.0
 */
final class ControllerContainer
{

    private array $instances = [];

    /**
     * Gets the given controller instance.
     *
     * @param class-string<Controller> $controllerClass
     *
     * @return Controller
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function get(string $controllerClass): Controller
    {
        return $this->instances[$controllerClass] ?? throw RuntimeException::controllerInstanceNotFound($controllerClass);
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
    public function has(string $class): bool
    {
        return isset($this->instances[$class]);
    }

    /**
     * Loads the given controller with the given parameters.
     *
     * @param Router $router
     * @param class-string<Controller> $controllerClass
     * @param array{'name': string, 'type': string[], 'default': mixed, 'query': array}[] $properties
     *
     * @return Controller
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function load(Router $router, string $controllerClass, array $properties): Controller
    {
        try {
            $injections = RouterUtil::getInjectionsForConstructor($controllerClass);
            $injections = RouterUtil::getInjectionValues($router, $injections, $controllerClass);

            $instance = $this->instances[$controllerClass] = new $controllerClass(...$injections);

            RouterUtil::injectProperties(
                $instance,
                RouterUtil::getInjectionValues($router, $properties, $controllerClass)
            );

            return $instance;
        } catch (ReflectionException $err) {
            throw RuntimeException::reflectionError($err, sprintf('Loading controller "%s" failed due to a reflection error.', $controllerClass));
        }
    }

}
