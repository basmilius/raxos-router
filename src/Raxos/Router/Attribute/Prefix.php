<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;

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
    public function __construct(protected string $path)
    {
    }

    /**
     * Gets the path.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getPath(): string
    {
        return $this->path;
    }

}
