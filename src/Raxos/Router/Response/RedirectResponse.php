<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeader, HttpResponseCode};
use Raxos\Http\Structure\HttpHeadersMap;

/**
 * Class RedirectResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class RedirectResponse extends Response
{

    /**
     * RedirectResponse constructor.
     *
     * @param string $destination
     * @param HttpHeadersMap $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $destination,
        HttpHeadersMap $headers = new HttpHeadersMap(),
        HttpResponseCode $responseCode = HttpResponseCode::FOUND
    )
    {
        $headers->set(HttpHeader::LOCATION, $this->destination);

        parent::__construct(
            $headers,
            $responseCode
        );
    }

}
