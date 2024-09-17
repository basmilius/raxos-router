<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Controller;

use Raxos\OldRouter\Response\{ResponseMethods, ResultMethods};
use Raxos\OldRouter\Router;

/**
 * Class Controller
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Controller
 * @since 1.0.0
 */
abstract class Controller
{

    use ResponseMethods;
    use ResultMethods;

    /**
     * Controller constructor.
     *
     * @param Router $router
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        protected readonly Router $router
    ) {}

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