<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Contract\Router\AttributeInterface;

/**
 * Class Controller
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
readonly class Controller implements AttributeInterface
{

    /**
     * Controller constructor.
     *
     * @param string $prefix
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $prefix = '/'
    ) {}

}
