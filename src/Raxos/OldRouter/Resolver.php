<?php
declare(strict_types=1);

namespace Raxos\OldRouter;

use JetBrains\PhpStorm\ArrayShape;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Http\HttpMethod;
use Raxos\OldRouter\Attribute\{AttributeInterface, FromQuery, Injected, Prefix, Route, SubController, Version};
use Raxos\OldRouter\Controller\Controller;
use Raxos\OldRouter\Error\{RegisterException, RouterException};
use Raxos\OldRouter\Route\RouteExecutor;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_merge_recursive;
use function array_multisort;
use function in_array;
use function is_subclass_of;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strlen;
use function strpos;
use function strtr;
use function substr;
use function usort;
use const ARRAY_FILTER_USE_KEY;
use const SORT_DESC;

/**
 * Class Resolver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter
 * @since 1.0.0
 */
class Resolver
{

    private const array ARRAYABLE_OPTIONS = ['middlewares', 'request'];

    protected array $callStack = [];
    protected array $controllerList = [];
    protected array $mappings = [];
    private array $resolverDidControllers = [];

    /**
     * Adds the given controller.
     *
     * @param Router $router
     * @param Controller|string $controller
     *
     * @throws RegisterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function addController(Router $router, Controller|string $controller): void
    {
        if (!is_subclass_of($controller, Controller::class)) {
            throw RegisterException::mappingFailed(sprintf('The given class "%s" is not a controller.', $controller::class));
        }

        if (!($controller instanceof Controller)) {
            $controller = new $controller($router);
        }

        $this->controllerList[] = $controller;
    }

    /**
     * Resolves the call stack for each route from the mappings.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function resolveCallStack(): void
    {
        $frames = array_merge_recursive(...array_map($this->resolveCallStackController(...), $this->mappings));
        $paths = array_map(strlen(...), array_keys($frames));
        array_multisort($paths, SORT_DESC, $frames);

        $this->callStack = $frames;
    }

    /**
     * Resolves the controller mappings.
     *
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function resolveMappings(): void
    {
        $this->resolverDidControllers = [];

        try {
            foreach ($this->controllerList as $controller) {
                $class = new ReflectionClass($controller);
                $mapping = $this->resolveControllerMapping($class);

                if ($mapping === null) {
                    continue;
                }

                $this->mappings[] = $mapping;
            }
        } catch (ReflectionException $err) {
            throw RegisterException::reflectionError($err, 'Could not map controllers because of a reflection error.');
        }
    }

    /**
     * Resolves the request into a route and returns null if nothing is found.
     *
     * @param HttpMethod $method
     * @param string $path
     * @param float $version
     *
     * @return RouteExecutor|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function resolveRequest(HttpMethod $method, string $path, float $version): ?RouteExecutor
    {
        $routes = array_keys($this->callStack);
        $routes = array_filter($routes, static fn(string $route): bool => str_starts_with($path, rtrim(substr($route, 0, strpos($route, '(') ?: strlen($route)), '?')));

        usort($routes, static function (string $a, string $b): int {
            if (str_contains($a, '(') && str_contains($b, '(')) {
                return strlen($b) <=> strlen($a);
            }

            if (str_contains($a, '(')) {
                return 1;
            }

            if (str_contains($b, '(')) {
                return -1;
            }

            return strlen($b) <=> strlen($a);
        });

        foreach ($routes as $route) {
            $callStack = $this->callStack[$route] ?? null;

            if ($callStack === null) {
                continue;
            }

            $frames = $callStack[$method->value] ?? $callStack[HttpMethod::ANY->value] ?? null;

            if ($frames === null && $method === HttpMethod::OPTIONS) {
                $key = array_key_first($callStack);
                $frames = $callStack[$key] ?? null;
            }

            if ($frames === null) {
                continue;
            }

            if ($path === $route || preg_match("#^{$route}$#", $path, $matches)) {
                $params = array_filter($matches ?? [], 'is_string', ARRAY_FILTER_USE_KEY);

                return new RouteExecutor($frames, $params, $version);
            }
        }

        return null;
    }

    /**
     * Converts the given attribute to an option.
     *
     * @param ReflectionAttribute $attribute
     *
     * @return array|null
     * @throws ReflectionException
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function convertAttribute(ReflectionAttribute $attribute): ?array
    {
        if (is_subclass_of($attribute->getName(), MiddlewareInterface::class)) {
            $middleware = new ReflectionClass($attribute->getName());

            return ['middlewares', [
                $attribute->getName(),
                $attribute->getArguments(),
                array_map(
                    RouterUtil::normalizeInjectable(...),
                    array_filter($middleware->getProperties(), static fn(ReflectionProperty $property) => !empty($property->getAttributes(Injected::class)))
                )
            ]];
        }

        if (!is_subclass_of($attribute->getName(), AttributeInterface::class)) {
            return null;
        }

        $attr = $attribute->newInstance();

        return match (true) {
            $attr instanceof Prefix => ['prefix', $attr->path],
            $attr instanceof SubController => ['child', $this->resolveControllerMapping(new ReflectionClass($attr->class))],
            $attr instanceof Version => ['version', [$attr->min, $attr->max]],
            $attr instanceof Route => ['request', [$attr->method->value, $attr->path]],
            default => null
        };
    }

    /**
     * Converts the given attributes to options.
     *
     * @param ReflectionAttribute[] $attributes
     * @param array $options
     *
     * @throws ReflectionException
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function convertAttributes(array $attributes, array &$options): void
    {
        foreach ($attributes as $attribute) {
            $result = $this->convertAttribute($attribute);

            if ($result === null) {
                continue;
            }

            if (in_array($result[0], self::ARRAYABLE_OPTIONS, true)) {
                $options[$result[0]][] = $result[1];
            } else {
                $options[$result[0]] = $result[1];
            }
        }
    }

    /**
     * Converts the request path to regex with the given params.
     *
     * @param array $request
     * @param array $params
     *
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function convertPath(array &$request, array $params): void
    {
        $path = $request[1];

        foreach ($params as $param) {
            $simpleType = null;

            foreach ($param['type'] as $type) {
                if (is_subclass_of($type, RouterParameterInterface::class)) {
                    $prefix = (array_key_exists('default', $param) ? '?' : '');
                    $regex = $type::getRouterRegex();
                    $regex = "{$prefix}(?<{$param['name']}>{$regex}){$prefix}";

                    $path = strtr($path, [
                        '$' . $param['name'] => $regex
                    ]);

                    continue 2;
                }

                if (!in_array($type, RouterUtil::SIMPLE_TYPES, true)) {
                    continue;
                }

                $simpleType = $type;
                break;
            }

            if ($simpleType === null) {
                continue;
            }

            $regex = $this->convertPathParam($param['name'], $simpleType, array_key_exists('default', $param));

            $path = strtr($path, [
                '$' . $param['name'] => $regex
            ]);
        }

        $request[] = $path;
    }

    /**
     * Converts the given param to regex.
     *
     * @param string $name
     * @param string $type
     * @param bool $defaultValue
     *
     * @return string
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function convertPathParam(string $name, string $type, bool $defaultValue): string
    {
        $regex = match ($type) {
            'string' => '[a-zA-Z0-9-_.@=,]+',
            'int' => '[0-9]+',
            'bool' => '(1|0|true|false)',
            default => throw RegisterException::mappingFailed('Parameter types used in route paths can only be simple types.')
        };

        $prefix = ($defaultValue ? '?' : '');

        return "{$prefix}(?<{$name}>{$regex})" . ($defaultValue ? '?' : '');
    }

    /**
     * Resolves the call stack for a single controller.
     *
     * @param array $controller
     * @param string|null $prefix
     * @param array $parents
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function resolveCallStackController(array $controller, ?string $prefix = null, array $parents = []): array
    {
        $prefix = $controller['prefix'] ?? $prefix ?? '';

        if ($prefix === '/') {
            $prefix = '';
        }

        return array_merge_recursive(...array_map(fn($route) => $this->resolveCallStackRoute($controller, $route, $prefix, $parents), $controller['routes']));
    }

    /**
     * Resolves the call stack for a single controller method.
     *
     * @param array $controller
     * @param array $route
     * @param string $prefix
     * @param array $parents
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function resolveCallStackRoute(array $controller, array $route, string $prefix, array $parents = []): array
    {
        $frames = [];

        $frame = [
            'class' => $route['class'],
            'method' => $route['method'],
            'type' => $route['type']
        ];

        if (isset($controller['middlewares']) || isset($route['middlewares'])) {
            $frame['middlewares'] = [
                ...($controller['middlewares'] ?? []),
                ...($route['middlewares'] ?? [])
            ];
        }

        if (isset($route['params'])) {
            $frame['params'] = $route['params'];
        }

        if (isset($controller['properties'])) {
            $frame['properties'] = $controller['properties'];
        }

        if (isset($route['version'])) {
            $frame['version'] = $route['version'];
        }

        foreach ($route['request'] as [$requestMethod, $path, $regex]) {
            $routeCall = array_merge($frame, [
                'request' => [$requestMethod, $path],
            ]);

            $routePath = $prefix . $regex;

            if ($routePath !== '/') {
                $routePath = rtrim($routePath, '/');
            }

            if (isset($route['child'])) {
                $frames = array_merge_recursive($frames, $this->resolveCallStackController($route['child'], $routePath, [...$parents, $routeCall]));
            } else {
                $frames[$routePath][$requestMethod] ??= [];
                $frames[$routePath][$requestMethod] = array_merge($frames[$routePath][$requestMethod], $parents);
                $frames[$routePath][$requestMethod][] = $routeCall;
            }
        }

        return $frames;
    }

    /**
     * Resolves the mappings for a single controller.
     *
     * @param ReflectionClass $class
     *
     * @return array|null
     * @throws ReflectionException
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function resolveControllerMapping(ReflectionClass $class): ?array
    {
        if (in_array($class->name, $this->resolverDidControllers, true)) {
            throw RegisterException::recursionDetected($class->name);
        }

        $this->resolverDidControllers[] = $class->name;

        $controllerAttributes = $class->getAttributes();
        $controllerMethods = $class->getMethods();

        $mapping = [
            'name' => $class->name,
            'routes' => [],
            'properties' => array_map(
                RouterUtil::normalizeInjectable(...),
                array_filter($class->getProperties(), static fn(ReflectionProperty $property) => !empty($property->getAttributes(Injected::class)))
            )
        ];

        $this->convertAttributes($controllerAttributes, $mapping);

        foreach ($controllerMethods as $method) {
            $methodMapping = $this->resolveMethodMapping($class, $method);

            if ($methodMapping === null) {
                continue;
            }

            $mapping['routes'][] = $methodMapping;
        }

        if (empty($mapping['routes'])) {
            return null;
        }

        return $mapping;
    }

    /**
     * Resolves the mappings for a single controller method.
     *
     * @param ReflectionClass $class
     * @param ReflectionMethod $method
     *
     * @return array|null
     * @throws ReflectionException
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function resolveMethodMapping(ReflectionClass $class, ReflectionMethod $method): ?array
    {
        $methodAttributes = $method->getAttributes();

        $mapping = [
            'class' => $class->name,
            'method' => $method->name,
            'request' => null
        ];

        $this->convertAttributes($methodAttributes, $mapping);

        if ($mapping['request'] === null) {
            return null;
        }

        if (!$method->hasReturnType()) {
            throw RegisterException::missingType(sprintf('Method "%s->%s()" should have a return type.', $class->name, $method->name));
        }

        $types = ($type = $method->getReturnType()) !== null ? ReflectionUtil::getTypes($type) ?? [] : [];

        if (isset($mapping['child']) && $types[0] !== 'void') {
            throw RegisterException::missingType(sprintf('Method "%s->%s()" should have a void return type.', $class->name, $method->name));
        }

        if ($method->getNumberOfParameters() > 0) {
            $params = [];
            $parameters = $method->getParameters();

            foreach ($parameters as $parameter) {
                $params[] = $this->resolveParameterMapping($class, $method, $parameter);
            }

            $mapping['params'] = $params;
        }

        foreach ($mapping['request'] as &$request) {
            $this->convertPath($request, $mapping['params'] ?? []);
        }

        unset($request);

        $mapping['type'] = $types;

        return $mapping;
    }

    /**
     * Resolves the mappings for a single parameter of a single controller method.
     *
     * @param ReflectionClass $class
     * @param ReflectionMethod $method
     * @param ReflectionParameter $parameter
     *
     * @return array
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[ArrayShape([
        'name' => 'string',
        'type' => 'string[]',
        'default' => 'mixed'
    ])]
    private function resolveParameterMapping(ReflectionClass $class, ReflectionMethod $method, ReflectionParameter $parameter): array
    {
        if (!$parameter->hasType()) {
            throw RegisterException::missingType(sprintf('Parameter "%s" of method "%s->%s()" should have a type.', $parameter->name, $class->name, $method->name));
        }

        $param = [
            'name' => $parameter->name,
            'type' => ($type = $parameter->getType()) !== null ? ReflectionUtil::getTypes($type) ?? [] : []
        ];

        $fromQueryAttributes = $parameter->getAttributes(FromQuery::class);

        if (isset($fromQueryAttributes[0])) {
            $param['query'] = $fromQueryAttributes[0]->getArguments()[0];
        }

        if ($parameter->isDefaultValueAvailable()) {
            $param['default'] = $parameter->getDefaultValue();
        }

        return $param;
    }

}
