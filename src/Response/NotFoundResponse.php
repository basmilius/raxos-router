<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;

/**
 * Class NotFoundResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class NotFoundResponse extends Response
{

    /**
     * NotFoundResponse constructor.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct()
    {
        parent::__construct(responseCode: HttpResponseCode::NOT_FOUND);
    }

}
