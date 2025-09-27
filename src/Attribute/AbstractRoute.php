<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Raxos\Contract\Router\AttributeInterface;
use Raxos\Http\HttpMethod;

/**
 * Class AbstractRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
abstract readonly class AbstractRoute implements AttributeInterface
{

    /**
     * AbstractRoute constructor.
     *
     * @param HttpMethod $method
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public HttpMethod $method,
        public string $path
    ) {}

}
