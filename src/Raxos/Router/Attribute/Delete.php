<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpMethods;

/**
 * Class Delete
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Delete extends Route
{

    /**
     * Delete constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    public function __construct(string $path = '/')
    {
        parent::__construct($path, HttpMethods::DELETE);
    }

}
