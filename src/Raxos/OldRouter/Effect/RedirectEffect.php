<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Effect;

use Raxos\Http\HttpResponseCode;
use Raxos\OldRouter\Router;

/**
 * Class RedirectEffect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Effect
 * @since 1.0.0
 */
final readonly class RedirectEffect extends Effect
{

    /**
     * RedirectEffect constructor.
     *
     * @param Router $router
     * @param string $destination
     * @param HttpResponseCode $responseCode
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        Router $router,
        public string $destination,
        public HttpResponseCode $responseCode = HttpResponseCode::TEMPORARY_REDIRECT
    )
    {
        parent::__construct($router);
    }

}
