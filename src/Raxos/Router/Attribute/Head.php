<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Http\HttpMethods;

/**
 * Class Head
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Head extends Route
{

    /**
     * Head constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(string $path)
    {
        parent::__construct($path, HttpMethods::HEAD);
    }

}
