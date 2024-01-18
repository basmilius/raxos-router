<?php
declare(strict_types=1);

namespace Raxos\Router\Error;

/**
 * Class RegisterException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Error
 * @since 1.0.0
 */
final class RegisterException extends RouterException
{

    public const int ERR_MAPPING_FAILED = 1;
    public const int ERR_MISSING_TYPE = 2;
    public const int ERR_ILLEGAL_TYPE = 4;
    public const int ERR_NOT_A_CONTROLLER = 8;
    public const int ERR_RECURSION_DETECTED = 16;

}
