<?php
declare(strict_types=1);

namespace Raxos\OldRouter;

/**
 * Interface RouterParameterInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter
 * @since 2.0.0
 */
interface RouterParameterInterface
{

    /**
     * Gets the regex that is used in the path.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.3
     */
    public static function getRouterRegex(): string;

    /**
     * Converts the string from the url into a type.
     *
     * @param string $value
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function getRouterValue(string $value): mixed;

}
