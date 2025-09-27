<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Contract\Router\{AttributeInterface, ValueProviderInterface};
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Request\Request;
use Raxos\Router\RouterUtil;

/**
 * Class MapHeader
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class MapHeader implements AttributeInterface, ValueProviderInterface
{

    /**
     * MapHeader constructor.
     *
     * @param string $header
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $header
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getRegex(Injectable $injectable): string
    {
        return RouterUtil::convertPathParam($injectable->name, 'string', $injectable->defaultValue->defined);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getValue(Request $request, Injectable $injectable): mixed
    {
        return $request->headers->get($this->header ?? $injectable->name) ?? $injectable->defaultValue->value;
    }
}
