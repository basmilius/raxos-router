<?php
declare(strict_types=1);

namespace Raxos\Router;

use BackedEnum;
use Generator;
use JetBrains\PhpStorm\Pure;
use Raxos\Foundation\Collection\Map;
use Raxos\Foundation\Contract\OptionInterface;
use Raxos\Foundation\Contract\StringParsableInterface;
use Raxos\Foundation\Option\{Option, OptionException};
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use ReflectionClass;
use ReflectionException;
use function array_any;
use function get_class;
use function gettype;
use function implode;
use function in_array;
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
            'int' => (int)$value,
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
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function getValue(Runner $runner, Request $request, Injectable $injectable, string $class, ?string $method = null): mixed
    {
        try {
            $valueKey = "{$injectable->name}:value";

            return Option::none()
                ->orElse(static fn() => self::getValueProviderCachedValue($request, $valueKey))
                ->orElse(static fn() => self::getValueProviderValue($request, $injectable, $valueKey))
                ->orElse(static fn() => self::getParameterValue($runner->router->globals, $injectable, $class, $method))
                ->orElse(static fn() => self::getParameterValue($request->parameters, $injectable, $class, $method))
                ->orElse(static fn() => self::getDefaultValue($injectable))
                ->orThrow(static fn() => RuntimeException::missingInjection($class, $method, $injectable->name, implode(', ', $injectable->types)))
                ->get();
        } catch (OptionException $err) {
            throw RuntimeException::unexpected($err, __METHOD__);
        }
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
     * @return Generator
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function getValues(Runner $runner, Request $request, array $injectables, string $class, ?string $method = null): Generator
    {
        foreach ($injectables as $injectable) {
            yield $injectable->name => self::getValue($runner, $request, $injectable, $class, $method);
        }
    }

    /**
     * Injects the given injectables as properties into the given instance.
     *
     * @param object $instance
     * @param Generator<string, mixed> $injectables
     *
     * @return void
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectClassProperties(object $instance, Generator $injectables): void
    {
        try {
            $class = new ReflectionClass($instance);

            foreach ($injectables as $injectableName => $injectableValue) {
                if (isset($instance->{$injectableName})) {
                    continue;
                }

                $property = $class->getProperty($injectableName);
                $property->setValue($instance, $injectableValue);
            }
        } catch (ReflectionException $err) {
            throw RuntimeException::reflectionError($err);
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
        $valueType = get_class($value);

        return array_any($types, static fn(string $type) => $valueType === $type || is_subclass_of($valueType, $type));
    }

    /**
     * Returns the default value.
     *
     * @param Injectable $injectable
     *
     * @return OptionInterface<mixed>
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private static function getDefaultValue(Injectable $injectable): OptionInterface
    {
        if ($injectable->defaultValue->defined) {
            return Option::some($injectable->defaultValue->value);
        }

        return Option::none();
    }

    /**
     * Returns a value from within the given set of parameters.
     *
     * @param Map $parameters
     * @param Injectable $injectable
     * @param string $class
     * @param string|null $method
     *
     * @return OptionInterface<mixed>
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private static function getParameterValue(Map $parameters, Injectable $injectable, string $class, ?string $method): OptionInterface
    {
        if (!$parameters->has($injectable->name)) {
            return Option::none();
        }

        $value = $parameters->get($injectable->name);

        if (!is_object($value)) {
            foreach ($injectable->types as $type) {
                if (is_subclass_of($type, StringParsableInterface::class)) {
                    return Option::some($type::fromString($value));
                }

                if (is_subclass_of($type, BackedEnum::class)) {
                    return Option::some($type::tryFrom($value));
                }

                if (!in_array($type, self::SIMPLE_TYPES)) {
                    continue;
                }

                return Option::some(self::convertValue($value, $type));
            }

            return Option::some($value);
        }

        if (self::isCorrectType($value, $injectable->types)) {
            return Option::some($value);
        }

        $types = implode(', ', $injectable->types);

        throw RuntimeException::invalidInjection($class, $method, $injectable->name, gettype($value), $types);
    }

    /**
     * Returns a cached value from a value provider.
     *
     * @param Request $request
     * @param string $valueKey
     *
     * @return OptionInterface<mixed>
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private static function getValueProviderCachedValue(Request $request, string $valueKey): OptionInterface
    {
        if ($request->parameters->has($valueKey)) {
            return Option::some($request->parameters->get($valueKey));
        }

        return Option::none();
    }

    /**
     * Returns an uncached value from a value provider.
     *
     * @param Request $request
     * @param Injectable $injectable
     * @param string $valueKey
     *
     * @return OptionInterface<mixed>
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    private static function getValueProviderValue(Request $request, Injectable $injectable, string $valueKey): OptionInterface
    {
        if ($injectable->valueProvider !== null) {
            $value = $injectable->valueProvider->getValue($request, $injectable);
            $request->parameters->set($valueKey, $value);

            return Option::some($value);
        }

        return Option::none();
    }

}
