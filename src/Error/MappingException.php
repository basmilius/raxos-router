<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

use Raxos\Foundation\Error\ExceptionId;
use Raxos\Router\Response\Response;
use ReflectionException;
use function sprintf;

/**
 * Class MappingException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 1.1.0
 */
final class MappingException extends RouterException
{

    /**
     * Returns the exception for when a regex for a parameter
     * could not be found.
     *
     * @param string $name
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function invalidPathParameter(string $name): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_mapping_invalid_path_parameter',
            sprintf('Could not determine regex for parameter with name "%s"', $name)
        );
    }

    /**
     * Returns the exception for when a controller method has an
     * invalid return type.
     *
     * @param string $class
     * @param string $name
     * @param string $expected
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function invalidReturnType(string $class, string $name, string $expected = Response::class): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_mapping_invalid_return_type',
            sprintf('Method "%s" of controller "%s" has an invalid return type. It should always return %s.', $name, $class, $expected)
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
            'router_mapping_reflection_error',
            'Mapping failed due to a reflection error.',
            $err
        );
    }

    /**
     * Returns the exception for when a type definition is too complex.
     *
     * @param string $name
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function typeComplex(string $name): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_mapping_type_complex',
            sprintf('Parameter "%s" has a type that is too complex.', $name)
        );
    }

    /**
     * Returns the exception for when a type definition is missing.
     *
     * @param string $class
     * @param string $name
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function typeRequired(string $class, string $name): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'router_mapping_type_required',
            sprintf('Parameter "%s" of controller "%s" needs a type definition.', $name, $class)
        );
    }

}
