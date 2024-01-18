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

    public const int ERR_INSTANCE_NOT_FOUND = 1;
    public const int ERR_EXCEPTION_IN_HANDLER = 2;
    public const int ERR_EXCEPTION_IN_MIDDLEWARE = 4;
    public const int ERR_INVALID_PARAMETER = 8;
    public const int ERR_MISSING_PARAMETER = 16;
    public const int ERR_REFLECTION_FAILED = 32;
    public const int ERR_VALIDATION_ERROR = 64;
    public const int ERR_VALIDATION_FAILED = 128;

}
