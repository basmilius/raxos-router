<?php
declare(strict_types=1);

namespace Raxos\OldRouter;

use Raxos\Http\Validate\Error\ValidatorException;
use Raxos\OldRouter\Effect\Effect;
use Raxos\OldRouter\Response\Response;

/**
 * Interface MiddlewareInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter
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
