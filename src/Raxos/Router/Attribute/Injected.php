<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;

/**
 * Class Injected
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Injected implements AttributeInterface {}
