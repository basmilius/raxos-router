<?php
declare(strict_types=1);

namespace Raxos\Router;

use JetBrains\PhpStorm\Pure;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Http\Body\HttpBodyJson;
use Raxos\Http\HttpRequest;
use Raxos\Http\Validate\Error\ValidatorException;
use Raxos\Http\Validate\RequestModel;
use Raxos\Http\Validate\Validator;
use Raxos\Router\Error\RouterException;
use Raxos\Router\Error\RuntimeException;
use ReflectionClass;
use ReflectionException;
use function array_key_exists;
use function get_class;
use function gettype;
use function implode;
use function in_array;
use function is_subclass_of;
use function sprintf;

/**
 * Class RouterUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.0.0
 */
final class RouterUtil
{

    public const SIMPLE_TYPES = ['string', 'bool', 'int'];

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
     * Prepares the parameters for a controller or controller method.
     *
     * @param Router $router
     * @param array $parameters
     * @param string $controller
     * @param string|null $method
     *
     * @return array
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function prepareParameters(Router $router, array $parameters, string $controller, ?string $method = null): array
    {
        $params = [];

        foreach ($parameters as $parameter) {
            $parameterName = $parameter['name'];
            $parameterType = $parameter['type'];

            if ($router->hasParameter($parameterName)) {
                $value = $router->getParameter($parameterName);
                $valueType = gettype($value);

                if ($valueType !== 'object') {
                    foreach ($parameterType as $type) {
                        if (!in_array($type, self::SIMPLE_TYPES)) {
                            continue;
                        }

                        $value = self::convertParameterType($type, $value);
                        break;
                    }
                } else {
                    $isCorrectType = false;
                    $valueType = get_class($value);

                    foreach ($parameterType as $type) {
                        if ($valueType === $type) {
                            $isCorrectType = true;
                            break;
                        }

                        if (is_subclass_of($valueType, $type)) {
                            $isCorrectType = true;
                            break;
                        }
                    }

                    if (!$isCorrectType) {
                        $parameterType = implode('|', $parameterType);

                        if ($method !== null) {
                            throw new RuntimeException(sprintf('Could not invoke controller method "%s::%s()", wrong type ("%s") for parameter "%s", should be "%s".', $controller, $method, $valueType, $parameterName, $parameterType), RuntimeException::ERR_INVALID_PARAMETER);
                        } else {
                            throw new RuntimeException(sprintf('Could not initialize controller "%s", wrong type ("%s") for parameter "%s", should be "%s".', $controller, $valueType, $parameterName, $parameterType), RuntimeException::ERR_INVALID_PARAMETER);
                        }
                    }
                }

                $params[] = $value;
            } else if (array_key_exists('default', $parameter)) {
                $params[] = $parameter['default'];
            } else if (isset($parameterType[0]) && is_subclass_of($parameterType[0], RequestModel::class)) {
                try {
                    $request = $router->getParameter('request');

                    if (!($request instanceof HttpRequest)) {
                        throw new RuntimeException(sprintf('Validation failed for controller method "%s::%s()". The $request global was not set or is not an instance of %s.', $controller, $method, HttpRequest::class), RuntimeException::ERR_VALIDATION_ERROR);
                    }

                    $body = $request->body();
                    $contentType = $request->contentType();

                    if ($contentType === 'application/json' && $body instanceof HttpBodyJson) {
                        $data = $body->array();
                    } else {
                        $data = $request->post()->array();

                        foreach ($request->files() as $key => $file) {
                            $data[$key] = $file;
                        }
                    }

                    /** @var string $requestModel */
                    $requestModel = $parameterType[0];

                    $params[] = Validator::validate($requestModel, $data);
                } catch (ValidatorException $err) {
                    throw new RuntimeException(sprintf('Validation failed for controller method "%s::%s()".', $controller, $method), RuntimeException::ERR_VALIDATION_FAILED, $err);
                }
            } else {
                $parameterType = implode('|', $parameterType);

                if ($method !== null) {
                    throw new RuntimeException(sprintf('Could not invoke controller method "%s::%s()", missing parameter "%s" with type "%s".', $controller, $method, $parameterName, $parameterType), RuntimeException::ERR_MISSING_PARAMETER);
                } else {
                    throw new RuntimeException(sprintf('Could not initialize controller "%s", missing parameter "%s" with type "%s".', $controller, $parameterName, $parameterType), RuntimeException::ERR_MISSING_PARAMETER);
                }
            }
        }

        return $params;
    }

    /**
     * Prepares the parameters for the given class.
     *
     * @param string $class
     *
     * @return array
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function prepareParametersForClass(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
            $parameters = $reflection->getConstructor()->getParameters();
            $params = [];

            foreach ($parameters as $parameter) {
                $types = ReflectionUtil::getTypes($parameter->getType()) ?? [];

                $param = [
                    'name' => $parameter->getName(),
                    'type' => $types
                ];

                if ($parameter->isDefaultValueAvailable()) {
                    $param['default'] = $parameter->getDefaultValue();
                }

                $params[] = $param;
            }

            return $params;
        } catch (ReflectionException $err) {
            throw new RuntimeException(sprintf('Reflection failed for class "%s".', $class), RuntimeException::ERR_REFLECTION_FAILED, $err);
        }
    }

}
