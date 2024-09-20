<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Error;

use Raxos\Foundation\Error\ExceptionId;
use ReflectionException;
use function sprintf;

/**
 * Class RegisterException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Error
 * @since 1.0.17
 */
final class RegisterException extends RouterException
{

    /**
     * Returns a mapping-failed exception.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function mappingFailed(string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_mapping_failed',
            $message);
    }

    /**
     * Returns a missing type exception.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function missingType(string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_missing_type',
            $message
        );
    }

    /**
     * Returns a recursion-detected exception.
     *
     * @param string $controllerClass
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function recursionDetected(string $controllerClass): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_recursion_detected',
            sprintf('Recursion detected! Controller class "%s" can only be used once.', $controllerClass)
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

}
