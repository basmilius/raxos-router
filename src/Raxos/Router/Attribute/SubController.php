<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Router\Controller\Controller;
use Raxos\Router\Error\RegisterException;
use function sprintf;

/**
 * Class SubController
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class SubController
{

    /**
     * SubController constructor.
     *
     * @param string $class
     *
     * @throws RegisterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(public string $class)
    {
        if (!is_subclass_of($class, Controller::class)) {
            throw RegisterException::mappingFailed(sprintf('Class "%s" is not a controller. Controllers should extend from "%s".', $class, Controller::class));
        }
    }

}
