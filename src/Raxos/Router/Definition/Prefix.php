<?php
declare(strict_types=1);

namespace Raxos\Router\Definition;

use Raxos\Foundation\Contract\SerializableInterface;

/**
 * Class Prefix
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Definition
 * @since 1.7.0
 */
final readonly class Prefix implements SerializableInterface
{

    /**
     * Prefix constructor.
     *
     * @param string $plain
     * @param string $regex
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __construct(
        public string $plain = '',
        public string $regex = ''
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __serialize(): array
    {
        return [
            $this->plain,
            $this->regex
        ];
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.7.0
     */
    public function __unserialize(array $data): void
    {
        [
            $this->plain,
            $this->regex
        ] = $data;
    }

}
