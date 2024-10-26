<?php
declare(strict_types=1);

namespace Raxos\Router;

use Generator;
use Raxos\Foundation\Util\ArrayUtil;
use Raxos\Router\Attribute\{AbstractRoute, Child, Controller, Injected};
use Raxos\Router\Contract\{AttributeInterface, MiddlewareInterface, ValueProviderInterface};
use Raxos\Router\Definition\{ControllerClass, DefaultValue, Injectable, Middleware, Route};
use Raxos\Router\Error\MappingException;
use Raxos\Router\Frame\{ControllerFrame, FrameStack, MiddlewareFrame, RouteFrame};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function array_filter;
use function array_keys;
use function array_map;
use function array_multisort;
use function ltrim;
use function rtrim;
use const SORT_DESC;

/**
 * Class Mapper
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 */
final class Mapper
{

    /**
     * Returns the route mapping for the given controllers.
     *
     * @param array $controllers
     *
     * @return array<string, array<string, FrameStack>>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function for(array $controllers): array
    {
        $result = [];

        foreach (self::generate($controllers) as $stack) {
            $result[$stack->path] ??= [];
            $result[$stack->path][$stack->method->name] ??= $stack;
        }

        $paths = array_map(\strlen(...), array_keys($result));
        array_multisort($paths, SORT_DESC, $result);

        return $result;
    }

    /**
     * Generates the given controllers.
     *
     * @param class-string[] $controllers
     *
     * @return Generator<FrameStack>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function generate(array $controllers): Generator
    {
        $controllers = self::controllers($controllers);

        foreach ($controllers as $controller) {
            yield from self::generateController($controller);
        }
    }

    /**
     * Generates the given controller.
     *
     * @param ControllerClass $controller
     * @param string $prefix
     * @param array $frames
     *
     * @return Generator<FrameStack>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function generateController(ControllerClass $controller, string $prefix = '', array $frames = []): Generator
    {
        $controllerPrefix = RouterUtil::convertPath($controller->prefix, $controller->parameters);
        $prefix = rtrim($prefix . $controllerPrefix, '/');
        $frames[] = new ControllerFrame($controller);

        foreach ($controller->middlewares as $middleware) {
            $frames[] = new MiddlewareFrame($middleware);
        }

        foreach ($controller->children as $child) {
            yield from self::generateController($child, $prefix, $frames);
        }

        foreach ($controller->routes as $route) {
            yield from self::generateRoute($route, $prefix, $frames);
        }
    }

    /**
     * Generates the given route.
     *
     * @param Route $route
     * @param string $prefix
     * @param array $frames
     *
     * @return Generator<FrameStack>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function generateRoute(Route $route, string $prefix = '', array $frames = []): Generator
    {
        foreach ($route->middlewares as $middleware) {
            $frames[] = new MiddlewareFrame($middleware);
        }

        $frames[] = new RouteFrame($route);

        foreach ($route->routes as $r) {
            $path = RouterUtil::normalizePath($r->path);
            $path = RouterUtil::convertPath($path, $route->parameters);
            $path = $prefix . $path;

            if ($path === '') {
                $path = '/';
            }

            yield new FrameStack($r->method, $path, $frames);
        }
    }

    /**
     * Returns the attributes of the given class, method, parameter or property.
     *
     * @param ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty $attributable
     *
     * @return AttributeInterface[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function attributes(ReflectionClass|ReflectionMethod|ReflectionParameter|ReflectionProperty $attributable): array
    {
        $attributes = $attributable->getAttributes(AttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        return array_map(static fn(ReflectionAttribute $attribute) => $attribute->newInstance(), $attributes);
    }

    /**
     * Returns a mapped controller.
     *
     * @param string $controller
     *
     * @return ControllerClass
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function controller(string $controller): ControllerClass
    {
        try {
            $class = new ReflectionClass($controller);

            $attributes = self::attributes($class);
            $prefix = '/' . ltrim(self::attributeOf($attributes, Controller::class)?->prefix ?? '', '/');

            $children = self::attributesOf($attributes, Child::class);
            $children = array_map(static fn(Child $child) => self::controller($child->controller), $children);

            $constructor = $class->getConstructor();
            $parameters = $constructor !== null ? self::injectablesForMethod($constructor) : [];

            return new ControllerClass(
                prefix: $prefix,
                class: $class->name,
                children: $children,
                injectables: self::injectablesForClass($class),
                middlewares: self::middlewares($class),
                parameters: $parameters,
                routes: self::routes($class)
            );
        } catch (ReflectionException $err) {
            throw MappingException::reflectionError($err);
        }
    }

    /**
     * Returns mapped controllers for the given classes.
     *
     * @param class-string[] $controllers
     *
     * @return ControllerClass[]
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function controllers(array $controllers): array
    {
        return array_map(self::controller(...), $controllers);
    }

    /**
     * Returns a mapped default value.
     *
     * @param ReflectionParameter|ReflectionProperty $property
     *
     * @return DefaultValue
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function defaultValue(ReflectionParameter|ReflectionProperty $property): DefaultValue
    {
        try {
            $defined = $property instanceof ReflectionParameter ? $property->isDefaultValueAvailable() : $property->hasDefaultValue();

            if (!$defined) {
                if ($property->getType()->allowsNull()) {
                    return DefaultValue::of(null);
                }

                return DefaultValue::none();
            }

            return DefaultValue::of($property->getDefaultValue());
        } catch (ReflectionException $err) {
            throw MappingException::reflectionError($err);
        }
    }

    /**
     * Returns a mapped injectable.
     *
     * @param ReflectionParameter|ReflectionProperty $property
     *
     * @return Injectable
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectable(ReflectionParameter|ReflectionProperty $property): Injectable
    {
        $types = RouterUtil::types($property->getType());

        if (empty($types)) {
            throw MappingException::typeRequired($property->class, $property->name);
        }

        $attributes = self::attributes($property);

        return new Injectable(
            name: $property->name,
            types: $types,
            defaultValue: self::defaultValue($property),
            valueProvider: ArrayUtil::first($attributes, static fn(AttributeInterface $attr) => $attr instanceof ValueProviderInterface)
        );
    }

    /**
     * Returns the injectables for the given controller.
     *
     * @param ReflectionClass $class
     *
     * @return Injectable[]
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectablesForClass(ReflectionClass $class): array
    {
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        $properties = array_filter($properties, static fn(ReflectionProperty $property) => !empty($property->getAttributes(Injected::class)));

        return array_map(self::injectable(...), $properties);
    }

    /**
     * Returns the injectables for the given method.
     *
     * @param ReflectionMethod $method
     *
     * @return Injectable[]
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectablesForMethod(ReflectionMethod $method): array
    {
        $parameters = $method->getParameters();

        return array_map(self::injectable(...), $parameters);
    }

    /**
     * Returns the mapped middleware.
     *
     * @param ReflectionAttribute $attribute
     *
     * @return Middleware
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function middleware(ReflectionAttribute $attribute): Middleware
    {
        try {
            $class = new ReflectionClass($attribute->getName());

            return new Middleware(
                class: $class->name,
                arguments: $attribute->getArguments(),
                injectables: self::injectablesForClass($class)
            );
        } catch (ReflectionException $err) {
            throw MappingException::reflectionError($err);
        }
    }

    /**
     * Returns the middlewares for the given class or method.
     *
     * @param ReflectionClass|ReflectionMethod $classOrMethod
     *
     * @return Middleware[]
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function middlewares(ReflectionClass|ReflectionMethod $classOrMethod): array
    {
        $middlewares = $classOrMethod->getAttributes(MiddlewareInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        return array_map(self::middleware(...), $middlewares);
    }

    /**
     * Returns a mapped route.
     *
     * @param ReflectionMethod $method
     * @param ReflectionClass $class
     *
     * @return Route
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function route(ReflectionMethod $method, ReflectionClass $class): Route
    {
        $attributes = self::attributes($method);
        $middlewares = self::middlewares($method);
        $parameters = self::injectablesForMethod($method);
        $routes = self::attributesOf($attributes, AbstractRoute::class);

        $returnType = RouterUtil::types($method->getReturnType());

        if (empty($returnType)) {
            throw MappingException::invalidReturnType($method->class, $method->name);
        }

        return new Route(
            class: $class->name,
            method: $method->name,
            routes: $routes,
            middlewares: $middlewares,
            parameters: $parameters
        );
    }

    /**
     * Returns the routes for the given class.
     *
     * @param ReflectionClass $class
     *
     * @return Route[]
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function routes(ReflectionClass $class): array
    {
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array_filter($methods, static fn(ReflectionMethod $method) => !empty($method->getAttributes(AttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF)));

        return array_map(static fn(ReflectionMethod $method) => self::route($method, $class), $methods);
    }

    /**
     * Returns the attribute of the given type.
     *
     * @template TAttributeClass of AttributeInterface
     *
     * @param AttributeInterface[] $attributes
     * @param class-string<TAttributeClass> $attributeClass
     *
     * @return TAttributeClass&AttributeInterface|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function attributeOf(array $attributes, string $attributeClass): ?AttributeInterface
    {
        return self::attributesOf($attributes, $attributeClass)[0] ?? null;
    }

    /**
     * Returns the attributes of the given type.
     *
     * @template TAttributeClass of AttributeInterface
     *
     * @param AttributeInterface[] $attributes
     * @param class-string<TAttributeClass> $attributeClass
     *
     * @return TAttributeClass[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function attributesOf(array $attributes, string $attributeClass): array
    {
        return array_filter($attributes, static fn(AttributeInterface $attr) => $attr instanceof $attributeClass);
    }

}
