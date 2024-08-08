<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;

/**
 * Class Injected
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 29/07/2024
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Injected
{
}
