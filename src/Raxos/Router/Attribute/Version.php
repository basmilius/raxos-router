<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Router\Error\RegisterException;
use function sprintf;

/**
 * Class Version
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Version
{

    /**
     * Version constructor.
     *
     * @param float|null $min
     * @param float|null $max
     *
     * @throws RegisterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        public ?float $min = null,
        public ?float $max = null
    )
    {
        if ($min !== null && $max !== null && $min >= $max) {
            throw new RegisterException(sprintf('Minimum version %g should be higher than maximum %g.', $min, $max), RegisterException::ERR_MAPPING_FAILED);
        }

        if ($min !== null && $min <= 0) {
            throw new RegisterException(sprintf('Minimum version %g should be higher than 0.', $min), RegisterException::ERR_MAPPING_FAILED);
        }

        if ($max !== null && $max <= 0) {
            throw new RegisterException(sprintf('Maximum version %g should be higher than 0.', $max), RegisterException::ERR_MAPPING_FAILED);
        }
    }

}
