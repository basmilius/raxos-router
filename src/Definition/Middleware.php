<?php
declare(strict_types=1);

namespace Raxos\Router\Definition;

use Raxos\Contract\Router\MiddlewareInterface;
use Raxos\Contract\SerializableInterface;

/**
 * Class Middleware
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Definition
 * @since 1.1.0
 */
final readonly class Middleware implements SerializableInterface
{

    /**
     * Middleware constructor.
     *
     * @param class-string<MiddlewareInterface> $class
     * @param array $arguments
     * @param Injectable[] $injectables
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $class,
        public array $arguments,
        public array $injectables
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __serialize(): array
    {
        return [
            $this->class,
            $this->arguments,
            $this->injectables
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
            $this->class,
            $this->arguments,
            $this->injectables
        ] = $data;
    }

}
