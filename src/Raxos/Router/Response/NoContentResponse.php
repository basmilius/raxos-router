<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeaders, HttpResponseCode};

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
     * @param HttpHeaders $headers
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        HttpHeaders $headers = new HttpHeaders()
    )
    {
        parent::__construct(
            $headers,
            HttpResponseCode::NO_CONTENT
        );
    }

}
