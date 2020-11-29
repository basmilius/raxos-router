<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

/**
 * Class RuntimeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 1.0.0
 */
final class RuntimeException extends RouterException
{

    public const ERR_INSTANCE_NOT_FOUND = 1;
    public const ERR_EXCEPTION_IN_HANDLER = 2;
    public const ERR_EXCEPTION_IN_MIDDLEWARE = 4;
    public const ERR_MISSING_PARAMETER = 8;
    public const ERR_REFLECTION_FAILED = 16;

}
