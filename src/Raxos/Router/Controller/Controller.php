<?php
declare(strict_types=1);

namespace Raxos\Router\Controller;

use Raxos\Router\Response\ResponseMethods;
use Raxos\Router\Router;

/**
 * Class Controller
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Controller
 * @since 1.0.0
 */
abstract class Controller
{

    use ResponseMethods;

    /**
     * Controller constructor.
     *
     * @param Router $router
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(protected Router $router)
    {
    }

    /**
     * Invokes a method in the current controller.
     *
     * @param string $method
     * @param mixed ...$params
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     * @internal
     */
    public function invoke(string $method, mixed ...$params): mixed
    {
        return $this->{$method}(...$params);
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
    protected final function parameter(string $name, mixed $value): static
    {
        $this->router->parameter($name, $value);

        return $this;
    }

}
