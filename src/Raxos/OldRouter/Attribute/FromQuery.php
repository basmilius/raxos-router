<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Attribute;

use Attribute;

/**
 * Class FromQuery
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.6
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class FromQuery implements AttributeInterface
{

    /**
     * FromQuery constructor.
     *
     * @param string $param
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.6
     */
    public function __construct(
        public string $param
    ) {}

}
