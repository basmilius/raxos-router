<?php
declare(strict_types=1);

namespace Raxos\Router;

use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpMethod;
use Raxos\Router\Controller\Controller;
use Raxos\Router\Controller\ControllerContainer;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Effect\NotFoundEffect;
use Raxos\Router\Error\RouterException;
use Raxos\Router\Response\ResponseRegistry;
use function array_key_exists;

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.0.0
 */
class Router extends Resolver
{

    public readonly ControllerContainer $controllers;

    protected bool $isSetupDone = false;

    private array $globals = [];
    private array $parameters = [];
    private ?ResponseRegistry $responseRegistry = null;

    /**
     * Router constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->controllers = new ControllerContainer();
    }

    /**
     * Gets the response registry.
     *
     * @return ResponseRegistry
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function &getResponseRegistry(): ResponseRegistry
    {
        $this->responseRegistry ??= new ResponseRegistry();

        return $this->responseRegistry;
    }

    /**
     * Gets a parameter value.
     *
     * @param string $name
     * @param mixed|null $defaultValue
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     *
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection // phpstorm suggests mixed|static, which is not possible.
     */
    public final function getParameter(string $name, mixed $defaultValue = null): mixed
    {
        if ($name === 'router') {
            return $this;
        }

        return $this->parameters[$name] ?? $this->globals[$name] ?? $defaultValue;
    }

    /**
     * Returns TRUE if the given parameter exists.
     *
     * @param string $name
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    public final function hasParameter(string $name): bool
    {
        return $name === 'router' || array_key_exists($name, $this->parameters) || array_key_exists($name, $this->globals);
    }

    /**
     * Adds the given controller to the resolver.
     *
     * @param Controller|string $controller
     *
     * @return $this
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function controller(Controller|string $controller): static
    {
        $this->addController($this, $controller);

        return $this;
    }

    /**
     * Adds a global parameter.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function global(string $name, mixed $value): static
    {
        $this->globals[$name] = $value;

        return $this;
    }

    /**
     * Adds a parameter.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function parameter(string $name, mixed $value): static
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Resolves the request and returns an effect if a route was found.
     *
     * @param HttpMethod $method
     * @param string $path
     * @param float $version
     *
     * @return Effect
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function resolve(HttpMethod $method, string $path, float $version = 1.0): Effect
    {
        if (!$this->isSetupDone) {
            $this->resolveMappings();
            $this->resolveCallStack();

            $this->isSetupDone = true;
        }

        $route = $this->resolveRequest($method, $path, $version);

        if ($route === null) {
            return new NotFoundEffect($this);
        }

        $this->responseRegistry = null;

        return $route->execute($this);
    }

}
