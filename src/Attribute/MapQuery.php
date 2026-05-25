<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use BackedEnum;
use Raxos\Contract\Router\{AttributeInterface, ValueProviderInterface};
use Raxos\Http\HttpRequest;
use Raxos\Router\{Error\ReflectionErrorException, Injector, RouterUtil};
use Raxos\Router\Definition\Injectable;
use ReflectionEnum;
use ReflectionException;
use function in_array;
use function is_array;
use function is_numeric;
use function is_subclass_of;

/**
 * Class MapQuery
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class MapQuery implements AttributeInterface, ValueProviderInterface
{

    /**
     * MapQuery constructor.
     *
     * @param string|null $key
     * @param class-string<BackedEnum>|null $enum
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public ?string $key = null,
        public ?string $enum = null
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getRegex(Injectable $injectable): string
    {
        return RouterUtil::convertPathParam($injectable->name, $injectable->types[0], $injectable->defaultValue->defined);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getValue(HttpRequest $request, Injectable $injectable): mixed
    {
        try {
            $result = $request->query->get($this->key ?? $injectable->name) ?? $injectable->defaultValue->value;
            $type = $injectable->types[0];

            if ($type === 'array' && !is_array($result)) {
                $result = $result === null ? [] : [$result];
            }

            if ($this->enum !== null && is_array($result)) {
                return self::castEnumArray($this->enum, $result);
            }

            if ($result !== null && is_subclass_of($type, BackedEnum::class)) {
                return self::castEnum($type, $result);
            }

            if ($result !== null && in_array($type, Injector::SIMPLE_TYPES)) {
                return Injector::convertValue($result, $type);
            }

            return $result;
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($err);
        }
    }

    /**
     * Casts a value to the given backed-enum, respecting the enum backing type.
     *
     * @param class-string<BackedEnum> $enumClass
     * @param mixed $value
     *
     * @return BackedEnum|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    private static function castEnum(string $enumClass, mixed $value): ?BackedEnum
    {
        if ($value instanceof $enumClass) {
            /** @var BackedEnum */
            return $value;
        }

        $backingType = new ReflectionEnum($enumClass)->getBackingType()?->getName();

        if ($backingType === 'int' && is_numeric($value)) {
            return $enumClass::tryFrom((int)$value);
        }

        return $enumClass::tryFrom((string)$value);
    }

    /**
     * Casts every value in the given list to the given backed-enum.
     * Invalid values are filtered out.
     *
     * @param class-string<BackedEnum> $enumClass
     * @param array $values
     *
     * @return BackedEnum[]
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    private static function castEnumArray(string $enumClass, array $values): array
    {
        $result = [];

        foreach ($values as $value) {
            $cast = self::castEnum($enumClass, $value);

            if ($cast !== null) {
                $result[] = $cast;
            }
        }

        return $result;
    }

}
