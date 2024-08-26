<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Attribute;

use Attribute;
use Raxos\Http\HttpMethod;

/**
 * Class Put
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Put extends Route
{

    /**
     * Put constructor.
     *
     * @param string $path
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(string $path = '/')
    {
        parent::__construct($path, HttpMethod::PUT);
    }

}
