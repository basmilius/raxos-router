<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use JetBrains\PhpStorm\Pure;

/**
 * Class With
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class With
{

    /**
     * With constructor.
     *
     * @param string $class
     * @param array $arguments
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    public function __construct(private string $class, private array $arguments = [])
    {
    }

    /**
     * Gets the middleware arguments.
     *
     * @return array
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Gets the middleware class.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getClass(): string
    {
        return $this->class;
    }

}
