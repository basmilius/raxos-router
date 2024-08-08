<?php
declare(strict_types=1);

namespace Raxos\Router;

use Raxos\Http\Validate\Error\ValidatorException;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;

/**
 * Interface MiddlewareInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router
 * @since 1.0.2
 */
interface MiddlewareInterface
{

    /**
     * Handles the request.
     *
     * @return Effect|Response|bool|null
     * @throws ValidatorException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.2
     */
    public function handle(): Effect|Response|bool|null;

}
