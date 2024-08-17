<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Exception;
use Raxos\Foundation\Error\ExceptionId;
use Raxos\Http\Validate\Error\ValidatorException;
use Raxos\Router\Controller\Controller;
use Raxos\Router\MiddlewareInterface;
use Raxos\Router\Route\RouteFrame;
use ReflectionException;
use function sprintf;

/**
 * Class RuntimeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 1.0.17
 */
final class RuntimeException extends RouterException
{

    /**
     * Returns a controller error exception.
     *
     * @param RouteFrame $frame
     * @param Exception $err
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function controllerError(RouteFrame $frame, Exception $err): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_controller_error',
            sprintf('Controller function "%s->%s()" threw an exception.', $frame->class, $frame->method),
            $err
        );
    }

    /**
     * Returns a controller instance not found exception.
     *
     * @param class-string<Controller> $controllerClass
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function controllerInstanceNotFound(string $controllerClass): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_controller_instance_not_found',
            sprintf('Instance of controller "%s" not found.', $controllerClass)
        );
    }

    /**
     * Returns a invalid parameter exception.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function invalidParameter(string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_invalid_parameter',
            $message
        );
    }

    /**
     * Returns a middleware error exception.
     *
     * @param class-string<MiddlewareInterface> $middlewareClass
     * @param Exception $err
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function middlewareError(string $middlewareClass, Exception $err): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_middleware_error',
            sprintf('Middleware function "%s" threw an exception.', $middlewareClass),
            $err
        );
    }

    /**
     * Returns a missing parameter exception.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function missingParameter(string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_missing_parameter',
            $message
        );
    }

    /**
     * Returns a reflection error exception.
     *
     * @param ReflectionException $err
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function reflectionError(ReflectionException $err, string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_reflection_error',
            $message,
            $err
        );
    }

    /**
     * Returns a validation error exception.
     *
     * @param ValidatorException $err
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function validationError(ValidatorException $err, string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_validation_error',
            $message,
            $err
        );
    }

}
