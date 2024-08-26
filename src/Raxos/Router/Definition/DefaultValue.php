<?php
declare(strict_types=1);

namespace Raxos\Router\Definition;

use Raxos\Foundation\Contract\SerializableInterface;

/**
 * Class DefaultValue
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Definition
 * @since 1.1.0
 */
final readonly class DefaultValue implements SerializableInterface
{

    /**
     * DefaultValue constructor.
     *
     * @param bool $defined
     * @param mixed $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public bool $defined,
        public mixed $value
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __serialize(): array
    {
        return [
            $this->defined,
            $this->value
        ];
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __unserialize(array $data): void
    {
        [
            $this->defined,
            $this->value
        ] = $data;
    }

    /**
     * Returns a definition without a value.
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function none(): self
    {
        return new self(false, null);
    }

    /**
     * Returns a definition with a value.
     *
     * @param mixed $value
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public static function of(mixed $value): self
    {
        return new self(true, $value);
    }

}
