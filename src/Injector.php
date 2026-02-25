<?php
declare(strict_types=1);

namespace Raxos\Router;

use BackedEnum;
use JetBrains\PhpStorm\Pure;
use Raxos\Collection\Map;
use Raxos\Contract\Container\ContainerExceptionInterface;
use Raxos\Contract\Router\RuntimeExceptionInterface;
use Raxos\Foundation\Contract\StringParsableInterface;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\{InvalidInjectionException, MissingInjectionException, ReflectionErrorException, UnexpectedException};
use Raxos\Router\Request\Request;
use ReflectionClass;
use ReflectionException;
use function array_any;
use function gettype;
use function implode;
use function in_array;
use function is_numeric;
use function is_object;
use function is_subclass_of;

/**
 * Class Injector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 * @internal
 * @private
 */
final class Injector
{

    public const array SIMPLE_TYPES = ['string', 'int', 'bool'];

    /**
     * Converts the value to the given type or returns NULL if that
     * could not be done.
     *
     * @param mixed $value
     * @param string $type
     *
     * @return string|int|bool|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    #[Pure]
    public static function convertValue(mixed $value, string $type): string|int|bool|null
    {
        return match ($type) {
            'string' => (string)$value,
            'int' => is_numeric($value) ? (int)$value : null,
            'bool' => $value === true || (int)$value === 1 || $value === 'true',
            default => null
        };
    }

    /**
     * Returns the value for the given injectable.
     *
     * @param Runner $runner
     * @param Request $request
     * @param Injectable $injectable
     * @param string $class
     * @param string|null $method
     *
     * @return mixed
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function getValue(Runner $runner, Request $request, Injectable $injectable, string $class, ?string $method = null): mixed
    {
        $valueKey = $injectable->name . ':value';

        // 1. Cached value from a value provider.
        if ($request->parameters->has($valueKey)) {
            return $request->parameters->get($valueKey);
        }

        // 2. Value from a value provider.
        if ($injectable->valueProvider !== null) {
            $value = $injectable->valueProvider->getValue($request, $injectable);
            $request->parameters->set($valueKey, $value);

            return $value;
        }

        // 3. Global parameters.
        if ($runner->router->globals->has($injectable->name)) {
            return self::resolveValue($runner->router->globals, $injectable, $class, $method);
        }

        // 4. Request parameters.
        if ($request->parameters->has($injectable->name)) {
            return self::resolveValue($request->parameters, $injectable, $class, $method);
        }

        // 5. Default value.
        if ($injectable->defaultValue->defined) {
            return $injectable->defaultValue->value;
        }

        // 6. Container dependency.
        if ($runner->router->container !== null) {
            try {
                return $runner->router->container->get($injectable->primaryType);
            } catch (ContainerExceptionInterface $err) {
                throw new UnexpectedException($err, __METHOD__);
            }
        }

        throw new MissingInjectionException($class, $method, $injectable->name, implode(', ', $injectable->types));
    }

    /**
     * Returns the values for the given injectables.
     *
     * @param Runner $runner
     * @param Request $request
     * @param Injectable[] $injectables
     * @param string $class
     * @param string|null $method
     *
     * @return array<string, mixed>
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function getValues(Runner $runner, Request $request, array $injectables, string $class, ?string $method = null): array
    {
        $values = [];

        foreach ($injectables as $injectable) {
            $values[$injectable->name] = self::getValue($runner, $request, $injectable, $class, $method);
        }

        return $values;
    }

    /**
     * Injects the given injectables as properties into the given instance.
     *
     * @param object $instance
     * @param array<string, mixed> $injectables
     *
     * @return void
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectClassProperties(object $instance, array $injectables): void
    {
        static $classes = [];
        static $properties = [];

        try {
            $className = $instance::class;
            $class = $classes[$className] ??= new ReflectionClass($className);

            foreach ($injectables as $injectableName => $injectableValue) {
                if (isset($instance->{$injectableName})) {
                    continue;
                }

                $propertyKey = $className . '::' . $injectableName;
                $property = $properties[$propertyKey] ??= $class->getProperty($injectableName);
                $property->setValue($instance, $injectableValue);
            }
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($err);
        }
    }

    /**
     * Returns TRUE if the given value matches one of the given types.
     *
     * @param mixed $value
     * @param string[] $types
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function isCorrectType(mixed $value, array $types): bool
    {
        $valueType = $value::class;

        return array_any($types, fn($type) => $valueType === $type || is_subclass_of($valueType, $type));
    }

    /**
     * Resolves the value from the given parameters map for the given injectable.
     *
     * @param Map $parameters
     * @param Injectable $injectable
     * @param string $class
     * @param string|null $method
     *
     * @return mixed
     * @throws RuntimeExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private static function resolveValue(Map $parameters, Injectable $injectable, string $class, ?string $method): mixed
    {
        $value = $parameters->get($injectable->name);

        if (!is_object($value)) {
            foreach ($injectable->types as $type) {
                if (is_subclass_of($type, StringParsableInterface::class)) {
                    return $type::fromString($value);
                }

                if (is_subclass_of($type, BackedEnum::class)) {
                    return $type::tryFrom($value);
                }

                if (!in_array($type, self::SIMPLE_TYPES)) {
                    continue;
                }

                return self::convertValue($value, $type);
            }

            return $value;
        }

        if (self::isCorrectType($value, $injectable->types)) {
            return $value;
        }

        throw new InvalidInjectionException($class, $method, $injectable->name, gettype($value), implode(', ', $injectable->types));
    }

}
