<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Attribute;

use Attribute;

/**
 * Class Injected
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Attribute
 * @since 1.0.17
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Injected implements AttributeInterface {}
