<?php
declare(strict_types=1);

namespace Raxos\Router\Contract;

/**
 * Interface InjectableInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Contract
 * @since 1.1.0
 */
interface InjectableInterface
{

    /**
     * Returns the regex used for the injectable.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function getRouterRegex(): string;

    /**
     * Returns the converted injectable value.
     *
     * @param string $value
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function getRouterValue(string $value): mixed;

}
