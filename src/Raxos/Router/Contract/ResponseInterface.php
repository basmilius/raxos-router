<?php
declare(strict_types=1);

namespace Raxos\Router\Contract;

use Raxos\Router\Error\RuntimeException;

/**
 * Interface ResponseInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Contract
 * @since 1.1.0
 */
interface ResponseInterface
{

    /**
     * Sends the response to the browser.
     *
     * @return void
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function send(): void;

}
