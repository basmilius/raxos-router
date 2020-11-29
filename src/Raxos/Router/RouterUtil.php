<?php
declare(strict_types=1);

namespace Raxos\Router;

use JetBrains\PhpStorm\Pure;
use Raxos\Router\Error\RouterException;
use Raxos\Router\Error\RuntimeException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use function in_array;
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
            'bool' => $value === '1' || $value === 'true',
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

                if (in_array($parameterType, self::SIMPLE_TYPES)) {
                    $value = self::convertParameterType($parameterType, $value);
                }

                $params[] = $value;
            } else if (isset($parameter['default'])) {
                $params[] = $parameter['default'];
            } else {
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
                /** @var ReflectionNamedType $parameterType */
                $parameterType = $parameter->getType();
                $parameterType = $parameterType->getName();

                $param = [
                    'name' => $parameter->getName(),
                    'type' => $parameterType
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
