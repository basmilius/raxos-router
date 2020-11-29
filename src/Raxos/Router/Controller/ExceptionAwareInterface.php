<?php
declare(strict_types=1);

namespace Raxos\Router\Controller;

use Exception;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;

/**
 * Interface ExceptionAwareInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\Router\Controller
 * @since 2.0.0
 */
interface ExceptionAwareInterface
{

    /**
     * Invoked when an exception is thrown within the current controller.
     *
     * @param Exception $err
     *
     * @return Effect|Response
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function onException(Exception $err): Effect|Response;

}
