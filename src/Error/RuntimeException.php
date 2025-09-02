<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Exception;
use Raxos\Foundation\Error\ExceptionId;
use Raxos\Http\Validate\Error\HttpValidatorException;
use Raxos\Router\Response\ResultResponse;
use ReflectionException;
use function sprintf;

/**
 * Class RuntimeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 1.1.0
 */
final class RuntimeException extends RouterException
{

    /**
     * Returns the exception for when a controller was not instantiated.
     *
     * @param string $controller
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function controllerNotInstantiated(string $controller): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_controller_not_instantiated',
            sprintf('Controller "%s" was not instantiated.', $controller)
        );
    }

    /**
     * Returns the exception for when an invalid handler is provided to redirect.
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function invalidHandler(): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_invalid_handler',
            'Route handler could not be found or is invalid.'
        );
    }

    /**
     * Returns the exception for an invalid injection.
     *
     * @param string $class
     * @param string|null $method
     * @param string $name
     * @param string $actualType
     * @param string $expectedType
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function invalidInjection(string $class, ?string $method, string $name, string $actualType, string $expectedType): self
    {
        if ($method !== null) {
            $message = sprintf('Could not inject parameter "%s" into "%s->%s()", wrong type "%s", expected "%s".', $name, $class, $method, $actualType, $expectedType);
        } else {
            $message = sprintf('Could not inject parameter "%s" into "%s", wrong type "%s", expected "%s".', $name, $class, $actualType, $expectedType);
        }

        return new self(
            ExceptionId::for(__METHOD__),
            'router_invalid_injection',
            $message
        );
    }

    /**
     * Returns the exception for when a file is missing.
     *
     * @param string $path
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function missingFile(string $path): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_missing_file',
            sprintf('File with path "%s" could not be found.', $path)
        );
    }

    /**
     * Returns the exception for a missing injection.
     *
     * @param string $class
     * @param string|null $method
     * @param string $name
     * @param string $expectedType
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function missingInjection(string $class, ?string $method, string $name, string $expectedType): self
    {
        if ($method !== null) {
            $message = sprintf('Could not inject parameter "%s" into "%s->%s()", because it was missing. Expected one with type "%s".', $name, $class, $method, $expectedType);
        } else {
            $message = sprintf('Could not inject parameter "%s" into "%s", because it was missing. Expected one with type "%s".', $name, $class, $expectedType);
        }

        return new self(
            ExceptionId::for(__METHOD__),
            'router_missing_injection',
            $message
        );
    }

    /**
     * Returns the exception for when an instance is missing.
     *
     * @param string $name
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function missingInstance(string $name): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_missing_instance',
            sprintf('Instance with name "%s" was missing.', $name)
        );
    }

    /**
     * Returns the exception for when the result response is trying to send
     * its response.
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function resultResponse(): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_result_response_unusable',
            sprintf('A response of type "%s" can not be used directly.', ResultResponse::class)
        );
    }

    /**
     * Returns the exception for when reflection failed.
     *
     * @param ReflectionException $err
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function reflectionError(ReflectionException $err): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'route_reflection_error',
            'Reflection error.',
            $err
        );
    }

    /**
     * Returns the exception for when an exception is thrown within a controller.
     *
     * @param Exception $err
     * @param string $call
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function unexpected(Exception $err, string $call): self
    {
        if ($err instanceof self) {
            return $err;
        }

        return new self(
            ExceptionId::for(__METHOD__),
            'route_unexpected_error',
            sprintf('Something went wrong while running "%s"', $call),
            $err
        );
    }

    /**
     * Returns a validation error exception.
     *
     * @param HttpValidatorException $err
     * @param string $class
     * @param string|null $method
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function validationError(HttpValidatorException $err, string $class, ?string $method): self
    {
        if ($method !== null) {
            $message = sprintf('Validation failed for "%s->%s()".', $class, $method);
        } else {
            $message = sprintf('Validation failed for "%s".', $class);
        }

        return new self(
            ExceptionId::for(__METHOD__),
            'router_validation_error',
            $message,
            $err
        );
    }

}
