<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Router\Contract\{AttributeInterface, ValueProviderInterface};
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Injector;
use Raxos\Router\Request\Request;
use Raxos\Router\RouterUtil;
use function in_array;
use function is_array;

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
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public ?string $key = null
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
    public function getValue(Request $request, Injectable $injectable): mixed
    {
        $result = $request->query->get($this->key ?? $injectable->name) ?? $injectable->defaultValue->value;

        if ($injectable->types[0] === 'array' && !is_array($result)) {
            return [$result];
        }

        if ($result !== null && in_array($injectable->types[0], Injector::SIMPLE_TYPES)) {
            return Injector::convertValue($result, $injectable->types[0]);
        }

        return $result;
    }

}
