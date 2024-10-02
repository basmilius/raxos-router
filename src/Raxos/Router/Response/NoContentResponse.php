<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;
use Raxos\Http\Structure\HttpHeadersMap;

/**
 * Class NoContentResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class NoContentResponse extends Response
{

    /**
     * NoContentResponse constructor.
     *
     * @param HttpHeadersMap $headers
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        HttpHeadersMap $headers = new HttpHeadersMap()
    )
    {
        parent::__construct(
            $headers,
            HttpResponseCode::NO_CONTENT
        );
    }

}
