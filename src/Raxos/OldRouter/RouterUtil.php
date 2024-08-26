<?php
declare(strict_types=1);

namespace Raxos\OldRouter;

use JetBrains\PhpStorm\{ArrayShape, Pure};
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Http\{HttpFile, HttpRequest};
use Raxos\Http\Body\HttpBodyJson;
use Raxos\Http\Validate\{RequestModel, Validator};
use Raxos\Http\Validate\Error\ValidatorException;
use Raxos\OldRouter\Error\{RouterException, RuntimeException};
use Raxos\OldRouter\Route\RouteFrame;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;
use function array_key_exists;
use function array_map;
use function get_class;
use function gettype;
use function implode;
use function in_array;
use function is_a;
use function is_subclass_of;
use function sprintf;

/**
 * Class RouterUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter
 * @since 1.0.0
 */
final class RouterUtil
{

    public const array SIMPLE_TYPES = ['string', 'bool', 'int'];

    /**
     * Converts the given value to the correct type.
     *
     * @param string $type
     * @param string $value
     *
     * @return string|int|bool|null
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    public static function convertParameterType(string $type, mixed $value): string|int|bool|null
    {
        return match ($type) {
            'string' => (string)$value,
            'int' => (int)$value,
            'bool' => $value === true || $value === '1' || $value === 'true',
            default => null
        };
    }

    /**
     * Gets the injections for the constructor of the given class name.
     *
     * @param string $className
     *
     * @return array
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public static function getInjectionsForConstructor(string $className): array
    {
        try {
            $classRef = new ReflectionClass($className);
            $constructor = $classRef->getConstructor();

            if ($constructor === null) {
                return [];
            }

            return array_map(self::normalizeInjectable(...), $constructor->getParameters());
        } catch (ReflectionException $err) {
            throw RuntimeException::reflectionError($err, sprintf('Could not get injections for constructor of class "%s".', $className));
        }
    }

    /**
     * Gets an injection value.
     *
     * @param Router $router
     * @param array{'name': string, 'type': string[], 'default': mixed, 'query': array} $injection
     * @param string $controllerClass
     * @param string|null $controllerMethod
     * @param RouteFrame|null $frame
     *
     * @return mixed
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public static function getInjectionValue(Router $router, array $injection, string $controllerClass, ?string $controllerMethod = null, ?RouteFrame $frame = null): mixed
    {
        $injectionName = $injection['name'];
        $injectionType = $injection['type'];
        $primaryType = $injectionType[0] ?? null;

        if (array_key_exists('query', $injection) && !$router->hasParameter($injection['query'])) {
            $request = self::requireRequest($router, $controllerClass, $controllerMethod);
            $router->parameter($injection['query'], $request->query->get($injection['query'], $injection['default'] ?? null));
        }

        if (is_a($primaryType, Router::class, true)) {
            return $router;
        }

        if (is_a($primaryType, RouteFrame::class, true) && $frame !== null) {
            return $frame;
        }

        if ($router->hasParameter($injectionName)) {
            $value = $router->getParameter($injectionName);
            $valueType = gettype($value);

            if ($valueType !== 'object') {
                foreach ($injectionType as $type) {
                    if (is_subclass_of($type, RouterParameterInterface::class)) {
                        $value = $type::getRouterValue($value);
                        break;
                    }

                    if (!in_array($type, self::SIMPLE_TYPES, true)) {
                        continue;
                    }

                    $value = self::convertParameterType($type, $value);
                    break;
                }

                return $value;
            }

            $isCorrectType = false;
            $valueType = get_class($value);

            foreach ($injectionType as $type) {
                if ($valueType === $type || is_subclass_of($valueType, $type)) {
                    $isCorrectType = true;
                    break;
                }
            }

            if (!$isCorrectType) {
                $injectionType = implode('|', $injectionType);

                if ($controllerMethod !== null) {
                    throw RuntimeException::invalidParameter(sprintf('Could not invoke controller method "%s->%s()", wrong type "%s" for parameter "%s", should be "%s".', $controllerClass, $controllerMethod, $valueType, $injectionName, $injectionType));
                }

                throw RuntimeException::invalidParameter(sprintf('Could not instantiate controller "%s", wrong type "%s" for parameter "%s", should be "%s".', $controllerClass, $valueType, $injectionName, $injectionType));
            }

            return $value;
        }

        if ($primaryType !== null && is_subclass_of($primaryType, RequestModel::class)) {
            try {
                $request = self::requireRequest($router, $controllerClass, $controllerMethod);
                $body = $request->body();
                $contentType = $request->contentType();

                if ($contentType === 'application/json' && $body instanceof HttpBodyJson) {
                    $data = $body->array();
                } else {
                    $data = $request->post->toArray();

                    /**
                     * @var string $key
                     * @var HttpFile $file
                     */
                    foreach ($request->files as $key => $file) {
                        if (!$file->isValid()) {
                            continue;
                        }

                        $data[$key] = $file;
                    }
                }

                /** @var class-string<RequestModel> $requestModel */
                $requestModel = $injectionType[0];

                return Validator::validate($requestModel, $data);
            } catch (ValidatorException $err) {
                throw RuntimeException::validationError($err, sprintf('Validation failed for controller method "%s->%s()".', $controllerClass, $controllerMethod));
            }
        }

        if (array_key_exists('default', $injection)) {
            return $injection['default'];
        }

        $injectionType = implode('|', $injectionType);

        if ($controllerMethod !== null) {
            throw RuntimeException::missingParameter(sprintf('Could not invoke controller method "%s->%s()", missing parameter "%s" with type "%s".', $controllerClass, $controllerMethod, $injectionName, $injectionType));
        }

        throw RuntimeException::missingParameter(sprintf('Could not initialize controller "%s", missing parameter "%s" with type "%s".', $controllerClass, $injectionName, $injectionType));
    }

    /**
     * Gets the injection values based on the given injections.
     *
     * @param Router $router
     * @param array{'name': string, 'type': string[], 'default': mixed, 'query': array}[] $injections
     * @param string $controllerClass
     * @param string|null $controllerMethod
     * @param RouteFrame|null $frame
     *
     * @return array
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public static function getInjectionValues(Router $router, array $injections, string $controllerClass, ?string $controllerMethod = null, ?RouteFrame $frame = null): array
    {
        $results = [];

        foreach ($injections as $injection) {
            $results[$injection['name']] = self::getInjectionValue($router, $injection, $controllerClass, $controllerMethod, $frame);
        }

        return $results;
    }

    /**
     * Injects the given injections to the given instance.
     *
     * @param object $instance
     * @param array $injections
     *
     * @return void
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public static function injectProperties(object $instance, array $injections): void
    {
        $classRef = new ReflectionClass($instance);

        foreach ($injections as $propertyName => $injectionValue) {
            if (isset($instance->{$propertyName})) {
                continue;
            }

            $propertyRef = $classRef->getProperty($propertyName);
            /** @noinspection PhpExpressionResultUnusedInspection */
            $propertyRef->setAccessible(true);
            $propertyRef->setValue($instance, $injectionValue);
        }
    }

    /**
     * Normalizes the given parameter or property.
     *
     * @param ReflectionParameter|ReflectionProperty $property
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    #[ArrayShape([
        'name' => 'string',
        'type' => 'string[]',
        'default' => 'mixed'
    ])]
    public static function normalizeInjectable(ReflectionParameter|ReflectionProperty $property): array
    {
        $types = ReflectionUtil::getTypes($property->getType()) ?? [];
        $param = [
            'name' => $property->getName(),
            'type' => $types
        ];

        if ($property instanceof ReflectionParameter && $property->isDefaultValueAvailable()) {
            $param['default'] = $property->getDefaultValue();
        }

        if ($property instanceof ReflectionProperty && $property->hasDefaultValue()) {
            $param['default'] = $property->getDefaultValue();
        }

        return $param;
    }

    /**
     * Ensures a request instance.
     *
     * @param Router $router
     * @param string $controllerClass
     * @param string|null $controllerMethod
     *
     * @return HttpRequest
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     * @see HttpRequest
     */
    private static function requireRequest(Router $router, string $controllerClass, ?string $controllerMethod = null): HttpRequest
    {
        $request = $router->getParameter('request');

        if ($request === null) {
            if ($controllerMethod !== null) {
                throw RuntimeException::missingParameter(sprintf('Controller method "%s->%s()" requires a $request injection of type "%s".', $controllerClass, $controllerMethod, HttpRequest::class));
            }

            throw RuntimeException::missingParameter(sprintf('Controller "%s" requires a $request injection of type "%s".', $controllerClass, HttpRequest::class));
        }

        return $request;
    }

}
