<?php
declare(strict_types=1);

namespace Raxos\Router\Definition;

use Raxos\Foundation\Contract\SerializableInterface;
use Raxos\Router\Contract\ValueProviderInterface;

/**
 * Class Injectable
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Definition
 * @since 1.1.0
 */
final readonly class Injectable implements SerializableInterface
{

    public string $primaryType;

    /**
     * Injectable constructor.
     *
     * @param string $name
     * @param array $types
     * @param DefaultValue $defaultValue
     * @param ValueProviderInterface|null $valueProvider
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $name,
        public array $types,
        public DefaultValue $defaultValue,
        public ?ValueProviderInterface $valueProvider
    )
    {
        $this->primaryType = $this->types[0];
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __serialize(): array
    {
        return [
            $this->name,
            $this->types,
            $this->defaultValue,
            $this->valueProvider
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
            $this->name,
            $this->types,
            $this->defaultValue,
            $this->valueProvider
        ] = $data;
    }

}
