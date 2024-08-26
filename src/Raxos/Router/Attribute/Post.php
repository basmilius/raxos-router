<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Http\HttpMethod;

/**
 * Class Post
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Post extends AbstractRoute
{

    /**
     * Post constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(string $path = '/')
    {
        parent::__construct(HttpMethod::POST, $path);
    }

}
