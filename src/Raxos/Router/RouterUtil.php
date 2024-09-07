<?php
declare(strict_types=1);

namespace Raxos\Router;

use JetBrains\PhpStorm\Pure;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Router\Contract\InjectableInterface;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\MappingException;
use ReflectionType;
use function ctype_alnum;
use function in_array;
use function is_subclass_of;
use function str_contains;
use function strlen;

/**
 * Class RouterUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 * @internal
 * @private
 */
final class RouterUtil
{

    /**
     * Converts the parameter placeholders in the given path to their
     * matching regexes.
     *
     * @param string $path
     * @param Injectable[] $injectables
     *
     * @return string
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function convertPath(string $path, array $injectables): string
    {
        foreach ($injectables as $injectable) {
            $name = $injectable->name;
            $regex = null;

            if (!str_contains($path, "\${$injectable->name}")) {
                continue;
            }

            if ($injectable->valueProvider !== null) {
                $regex = $injectable->valueProvider->getRegex($injectable);
            } else {
                foreach ($injectable->types as $type) {
                    if (is_subclass_of($type, InjectableInterface::class)) {
                        $regex = self::regex($type::getRouterRegex(), $injectable->name, $injectable->defaultValue->defined);
                        continue;
                    }

                    if (!in_array($type, Injector::SIMPLE_TYPES)) {
                        continue;
                    }

                    $regex = self::convertPathParam($injectable->name, $type, $injectable->defaultValue->defined);
                    break;
                }
            }

            if ($regex === null) {
                throw MappingException::invalidPathParameter($injectable->name);
            }

            $path = strtr($path, [
                "\${$name}" => $regex
            ]);
        }

        return $path;
    }

    /**
     * Returns the regexp for the given param.
     *
     * @param string $name
     * @param string $type
     * @param bool $isOptional
     *
     * @return string
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function convertPathParam(string $name, string $type, bool $isOptional): string
    {
        return match ($type) {
            'string' => self::regex('[a-zA-Z0-9-_.@=,]+', $name, $isOptional),
            'int' => self::regex('[0-9]+', $name, $isOptional),
            'bool' => self::regex('(1|0|true|false)', $name, $isOptional),
            default => throw MappingException::typeComplex($name)
        };
    }

    /**
     * Normalizes the given path.
     *
     * @param string $path
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    #[Pure]
    public static function normalizePath(string $path): string
    {
        if ($path === '/') {
            return '';
        }

        if ($path[0] !== '$' && !ctype_alnum($path[0])) {
            return $path;
        }

        return '/' . $path;
    }

    /**
     * Returns a regex group.
     *
     * @param string $regex
     * @param string $name
     * @param bool $isOptional
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    #[Pure]
    public static function regex(string $regex, string $name, bool $isOptional): string
    {
        $optional = $isOptional ? '?' : '';

        return "{$optional}(?<{$name}>{$regex}){$optional}";
    }

    /**
     * Sorts the given paths.
     *
     * @param string $a
     * @param string $b
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    #[Pure]
    public static function routeSorter(string $a, string $b): int
    {
        $aParenthesis = str_contains($a, '(');
        $bParenthesis = str_contains($b, '(');

        if ($aParenthesis && $bParenthesis) {
            return strlen($a) <=> strlen($b);
        }

        if ($aParenthesis) {
            return 1;
        }

        if ($bParenthesis) {
            return -1;
        }

        return strlen($a) <=> strlen($b);
    }

    /**
     * Returns the given type as an array.
     *
     * @param ReflectionType|null $type
     *
     * @return string[]
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function types(?ReflectionType $type): array
    {
        if ($type === null) {
            return [];
        }

        return ReflectionUtil::getTypes($type) ?? [];
    }

}
