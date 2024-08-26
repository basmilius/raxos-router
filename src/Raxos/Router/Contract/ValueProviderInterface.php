<?php
declare(strict_types=1);

namespace Raxos\Router\Contract;

use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\{MappingException, RuntimeException};
use Raxos\Router\Request\Request;

/**
 * Interface ValueProviderInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Contract
 * @since 1.1.0
 */
interface ValueProviderInterface
{

    /**
     * Returns the regex used for the value provider.
     *
     * @param Injectable $injectable
     *
     * @return string
     * @throws MappingException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getRegex(Injectable $injectable): string;

    /**
     * Returns the corresponding value.
     *
     * @param Request $request
     * @param Injectable $injectable
     *
     * @return mixed
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getValue(Request $request, Injectable $injectable): mixed;

}
