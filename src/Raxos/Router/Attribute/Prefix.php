<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use JetBrains\PhpStorm\Pure;

/**
 * Class Prefix
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Prefix
{

    /**
     * Prefix constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    public function __construct(public readonly string $path)
    {
    }

}
