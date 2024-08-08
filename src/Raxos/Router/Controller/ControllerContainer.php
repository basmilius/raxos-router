<?php
declare(strict_types=1);

namespace Raxos\Router\Controller;

use Raxos\Router\{Router, RouterUtil};
use Raxos\Router\Error\{RouterException, RuntimeException};
use ReflectionException;
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
    public function get(string $class): Controller
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
    public function has(string $class): bool
    {
        return isset($this->instances[$class]);
    }

    /**
     * Loads the given controller with the given parameters.
     *
     * @param Router $router
     * @param string $class
     * @param array{'name': string, 'type': string[], 'default': mixed, 'query': array}[] $properties
     *
     * @return Controller
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function load(Router $router, string $class, array $properties): Controller
    {
        try {
            $injections = RouterUtil::getInjectionsForConstructor($class);
            $injections = RouterUtil::getInjectionValues($router, $injections, $class);

            $instance = $this->instances[$class] = new $class(...$injections);

            RouterUtil::injectProperties(
                $instance,
                RouterUtil::getInjectionValues($router, $properties, $class)
            );

            return $instance;
        } catch (ReflectionException $err) {
            throw new RuntimeException(sprintf('Loading controller "%s" failed due to a reflection exception.', $class), RuntimeException::ERR_REFLECTION_FAILED, $err);
        }
    }

}
