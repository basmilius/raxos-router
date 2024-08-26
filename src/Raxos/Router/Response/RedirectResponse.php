<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeader, HttpHeaders, HttpResponseCode};

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
     * @param HttpHeaders $headers
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $destination,
        HttpHeaders $headers = new HttpHeaders(),
        HttpResponseCode $responseCode = HttpResponseCode::FOUND
    )
    {
        parent::__construct(
            $headers->set(HttpHeader::LOCATION, $this->destination),
            $responseCode
        );
    }

}
