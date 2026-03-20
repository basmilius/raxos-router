<?php
declare(strict_types=1);

namespace Raxos\Router;

use BackedEnum;
use JetBrains\PhpStorm\Pure;
use Raxos\Contract\Router\MappingExceptionInterface;
use Raxos\Foundation\Contract\StringParsableInterface;
use Raxos\Foundation\Util\ReflectionUtil;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\InvalidPathParameterException;
use Raxos\Router\Error\TypeTooComplexException;
use ReflectionType;
use UnitEnum;
use function array_map;
use function implode;
use function in_array;
use function is_subclass_of;
use function str_contains;
use function str_replace;
use function strlen;
use function usort;

/**
 * Class RouterUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.1.0
 */
final class RouterUtil
{

    private const array SIMPLE_TYPE_PATTERNS = [
        'string' => '[\w.@=,-]+',
        'int' => '\d+',
        'float' => '\d+(?:\.\d+)?',
        'bool' => 'true|false|[01]'
    ];

    /**
     * Builds a combined regex and key-index map for a single segment-count
     * group of dynamic routes. The resulting pattern uses PCRE alternation
     * so that all routes are tested with one {@see preg_match} call. Each
     * alternative ends with `(*MARK:N)` — a PCRE backtracking-control verb
     * placed after the route sub-pattern so that, upon a successful match,
     * `$matches['MARK']` is set to the index `N` of the winning alternative.
     * The `J` flag (PCRE_DUPNAMES) is applied to allow routes that share
     * the same named sub-pattern (e.g. `(?<id>\d+)`) without triggering a
     * PCRE compilation error; only the named group from the matching
     * alternative will be non-empty in the result.
     *
     * @param array<string, mixed> $routes
     *
     * @return array{0: string, 1: string[]}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.1
     */
    public static function buildGroupedRegex(array $routes): array
    {
        $keys = [];
        $patterns = [];

        foreach (array_keys($routes) as $route) {
            $index = count($keys);
            $keys[] = $route;
            $patterns[] = "(?:{$route})(*MARK:{$index})";
        }

        return ['#^(?:' . implode('|', $patterns) . ')$#J', $keys];
    }

    /**
     * Builds a combined regex and key-index map for every segment-count
     * group in the given dynamic routes map.
     *
     * @param array<int, array<string, mixed>> $dynamicRoutes
     *
     * @return array<int, array{0: string, 1: string[]}>
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.1
     */
    public static function buildGroupedRegexes(array $dynamicRoutes): array
    {

        return array_map(self::buildGroupedRegex(...), $dynamicRoutes);
    }

    /**
     * Converts the parameter placeholders in the given path to their
     * matching regexes.
     *
     * @param string $path
     * @param Injectable[] $injectables
     *
     * @return string
     * @throws MappingExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function convertPath(string $path, array $injectables): string
    {
        if (empty($injectables)) {
            return $path;
        }

        usort($injectables, static fn(Injectable $a, Injectable $b) => strlen($b->name) <=> strlen($a->name));

        foreach ($injectables as $injectable) {
            if (!str_contains($path, "\${$injectable->name}")) {
                continue;
            }

            $regex = $injectable->valueProvider?->getRegex($injectable);

            if ($regex !== null) {
                $path = str_replace("\${$injectable->name}", $regex, $path);

                continue;
            }

            foreach ($injectable->types as $type) {
                if (is_subclass_of($type, StringParsableInterface::class)) {
                    $regex = self::regex($type::pattern(), $injectable->name, $injectable->defaultValue->defined);
                    continue;
                }

                if (is_subclass_of($type, BackedEnum::class)) {
                    $regex = self::regex(implode('|', array_map(fn(UnitEnum $enum) => $enum->value, $type::cases())), $injectable->name, $injectable->defaultValue->defined);
                    continue;
                }

                if (!in_array($type, Injector::SIMPLE_TYPES, true)) {
                    continue;
                }

                $regex = self::convertPathParam($injectable->name, $type, $injectable->defaultValue->defined);
                break;
            }

            if ($regex === null) {
                throw new InvalidPathParameterException($injectable->name);
            }

            $path = str_replace("\${$injectable->name}", $regex, $path);
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
     * @throws MappingExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function convertPathParam(string $name, string $type, bool $isOptional): string
    {
        $pattern = self::SIMPLE_TYPE_PATTERNS[$type] ?? throw new TypeTooComplexException($name);

        return self::regex($pattern, $name, $isOptional);
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
    public static function normalizePath(string $path): string
    {
        if ($path === '/' || $path === '') {
            return '';
        }

        if ($path[0] !== '$' && !ctype_alnum($path[0])) {
            return $path;
        }

        return '/' . $path;
    }

    /**
     * Converts a path into segments.
     *
     * @param string $path
     *
     * @return string[]
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function pathToSegments(string $path): array
    {
        $segments = [];
        $current = '';
        $depth = 0;

        for ($i = 0, $len = strlen($path); $i < $len; $i++) {
            $char = $path[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
            } elseif ($char === ')') {
                $depth--;
                $current .= $char;
            } elseif ($char === '/' && $depth === 0) {
                if ($current !== '') {
                    $segments[] = $current;
                    $current = '';
                }
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $segments[] = $current;
        }

        return $segments;
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
        if ($isOptional) {
            return "?(?<{$name}>{$regex})?";
        }

        return "(?<{$name}>{$regex})";
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
    public static function routeSorter(string $a, string $b): int
    {
        $aParenthesis = str_contains($a, '(');
        $bParenthesis = str_contains($b, '(');

        if ($aParenthesis === $bParenthesis) {
            return strlen($a) <=> strlen($b);
        }

        return $aParenthesis ? 1 : -1;
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
