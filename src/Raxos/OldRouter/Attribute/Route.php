<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Attribute;

use Attribute;
use Raxos\Http\HttpMethod;

/**
 * Class Route
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Route implements AttributeInterface
{

    /**
     * Route constructor.
     *
     * @param string $path
     * @param HttpMethod $method
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        public string $path = '/',
        public HttpMethod $method = HttpMethod::ANY
    ) {}

}
