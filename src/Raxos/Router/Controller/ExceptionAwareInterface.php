<?php
declare(strict_types=1);

namespace Raxos\Router\Controller;

use Exception;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;

/**
 * Interface ExceptionAwareInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Controller
 * @since 1.0.0
 */
interface ExceptionAwareInterface
{

    /**
     * Invoked when an exception is thrown within the current controller.
     *
     * @param Exception $err
     *
     * @return Effect|Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function onException(Exception $err): Effect|Response;

}
