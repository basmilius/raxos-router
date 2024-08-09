<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpMethod;

/**
 * Class Route
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
readonly class Route
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
    #[Pure]
    public function __construct(
        public string $path = '/',
        public HttpMethod $method = HttpMethod::ANY
    ) {}

}
