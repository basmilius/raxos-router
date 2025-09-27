<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Contract\Router\AttributeInterface;

/**
 * Class Child
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Child implements AttributeInterface
{

    /**
     * Child constructor.
     *
     * @param class-string $controller
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $controller
    ) {}

}
