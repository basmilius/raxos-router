<?php
declare(strict_types=1);

namespace Raxos\Router;

use Generator;
use Raxos\Foundation\Util\ArrayUtil;
use Raxos\Router\Attribute\{AbstractRoute, Child, Controller, Injected};
use Raxos\Router\Contract\{AttributeInterface, MiddlewareInterface, ValueProviderInterface};
use Raxos\Router\Definition\{ControllerClass, DefaultValue, Injectable, Middleware, Prefix, Route};
use Raxos\Router\Error\MappingException;
use Raxos\Router\Frame\{ControllerFrame, FrameStack, MiddlewareFrame, RouteFrame};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function array_filter;
use function array_map;
use function count;
use function iterator_to_array;
use function ltrim;
use function rtrim;
use function strlen;
use function uksort;

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
     * @return array<array<string, array<string, FrameStack>>>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function for(array $controllers): array
    {
        $dynamicRoutes = [];
        $staticRoutes = [];

        foreach (self::generate($controllers) as $stack) {
            if ($stack->isDynamic) {
                $dynamicRoutes[$stack->path]['segments'] ??= RouterUtil::pathToSegments($stack->path);
                $dynamicRoutes[$stack->path][$stack->method->name] = $stack;
            } else {
                $staticRoutes[$stack->path][$stack->method->name] = $stack;
            }
        }

        uksort($dynamicRoutes, static fn(string $a, string $b) => strlen($b) <=> strlen($a));
        uksort($staticRoutes, static fn(string $a, string $b) => strlen($b) <=> strlen($a));

        $groupedDynamicRoutes = [];

        foreach ($dynamicRoutes as $route => $data) {
            $segmentCount = count($data['segments']);
            $groupedDynamicRoutes[$segmentCount][$route] = $data;
        }

        return [
            $groupedDynamicRoutes,
            $staticRoutes
        ];
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
            yield from self::generateController($controller, new Prefix());
        }
    }

    /**
     * Generates the given controller.
     *
     * @param ControllerClass $controller
     * @param Prefix $prefix
     * @param array $frames
     *
     * @return Generator<FrameStack>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function generateController(ControllerClass $controller, Prefix $prefix, array $frames = []): Generator
    {
        $prefix = new Prefix(
            plain: rtrim($prefix->plain . $controller->prefix, '/'),
            regex: rtrim($prefix->regex . RouterUtil::convertPath($controller->prefix, $controller->parameters), '/')
        );

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
     * @param Prefix $prefix
     * @param array $frames
     *
     * @return Generator<FrameStack>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function generateRoute(Route $route, Prefix $prefix, array $frames = []): Generator
    {
        foreach ($route->middlewares as $middleware) {
            $frames[] = new MiddlewareFrame($middleware);
        }

        $frames[] = new RouteFrame($route);

        foreach ($route->routes as $r) {
            $path = RouterUtil::normalizePath($r->path);

            $pathPlain = $prefix->plain . $path;

            if ($pathPlain === '') {
                $pathPlain = '/';
            }

            $path = RouterUtil::convertPath($path, $route->parameters);
            $path = $prefix->regex . $path;

            if ($path === '') {
                $path = '/';
            }

            yield new FrameStack($r->method, $path, $pathPlain, $frames);
        }
    }

    /**
     * Returns the attributes of the given class, method, parameter or property.
     *
     * @param ReflectionClass|ReflectionFunctionAbstract|ReflectionParameter|ReflectionProperty $attributable
     *
     * @return AttributeInterface[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function attributes(ReflectionClass|ReflectionFunctionAbstract|ReflectionParameter|ReflectionProperty $attributable): array
    {
        $attributes = $attributable->getAttributes(AttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as &$attribute) {
            $attribute = $attribute->newInstance();
        }

        return $attributes;
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
        static $cache = [];

        if (isset($cache[$controller])) {
            return $cache[$controller];
        }

        try {
            $class = new ReflectionClass($controller);

            $attributes = self::attributes($class);
            $prefix = '/' . ltrim(self::attributeOf($attributes, Controller::class)?->prefix ?? '', '/');

            $children = self::attributesOf($attributes, Child::class);
            $children = array_map(static fn(Child $child) => self::controller($child->controller), $children);

            $constructor = $class->getConstructor();
            $parameters = $constructor !== null ? iterator_to_array(self::injectablesForMethod($constructor)) : [];

            return $cache[$controller] = new ControllerClass(
                prefix: $prefix,
                class: $class->name,
                children: $children,
                injectables: iterator_to_array(self::injectablesForClass($class)),
                middlewares: iterator_to_array(self::middlewares($class)),
                parameters: $parameters,
                routes: iterator_to_array(self::routes($class))
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
     * @return Generator<ControllerClass>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function controllers(array $controllers): Generator
    {
        foreach ($controllers as $controller) {
            yield self::controller($controller);
        }
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
     * @return Generator<Injectable>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectablesForClass(ReflectionClass $class): Generator
    {
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        $properties = array_filter($properties, static fn(ReflectionProperty $property) => !empty($property->getAttributes(Injected::class)));

        foreach ($properties as $property) {
            yield self::injectable($property);
        }
    }

    /**
     * Returns the injectables for the given method.
     *
     * @param ReflectionFunctionAbstract $method
     *
     * @return Generator<Injectable>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function injectablesForMethod(ReflectionFunctionAbstract $method): Generator
    {
        $parameters = $method->getParameters();

        foreach ($parameters as $parameter) {
            yield self::injectable($parameter);
        }
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
        static $cache = [];

        $arguments = $attribute->getArguments();
        $name = $attribute->getName();

        if (empty($arguments) && isset($cache[$name])) {
            return $cache[$name];
        }

        try {
            $class = new ReflectionClass($name);

            return $cache[$name] = new Middleware(
                class: $class->name,
                arguments: $arguments,
                injectables: iterator_to_array(self::injectablesForClass($class))
            );
        } catch (ReflectionException $err) {
            throw MappingException::reflectionError($err);
        }
    }

    /**
     * Returns the middlewares for the given class or method.
     *
     * @param ReflectionClass|ReflectionFunctionAbstract $classOrMethod
     *
     * @return Generator<Middleware>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function middlewares(ReflectionClass|ReflectionFunctionAbstract $classOrMethod): Generator
    {
        $middlewares = $classOrMethod->getAttributes(MiddlewareInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($middlewares as $middleware) {
            yield self::middleware($middleware);
        }
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
        static $cache = [];

        $key = $class->name . '@' . $method->name;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $attributes = self::attributes($method);
        $middlewares = iterator_to_array(self::middlewares($method));
        $parameters = iterator_to_array(self::injectablesForMethod($method));
        $routes = self::attributesOf($attributes, AbstractRoute::class);

        $returnType = RouterUtil::types($method->getReturnType());

        if (empty($returnType)) {
            throw MappingException::invalidReturnType($method->class, $method->name);
        }

        return $cache[$key] = new Route(
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
     * @return Generator<Route>
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function routes(ReflectionClass $class): Generator
    {
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array_filter($methods, static fn(ReflectionMethod $method) => !empty($method->getAttributes(AttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF)));

        foreach ($methods as $method) {
            yield self::route($method, $class);
        }
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
