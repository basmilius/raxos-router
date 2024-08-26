<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Attribute;

use Attribute;

/**
 * Class Prefix
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Prefix implements AttributeInterface
{

    /**
     * Prefix constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        public string $path
    ) {}

}
