<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Raxos\Foundation\Contract\DebuggableInterface;
use Raxos\Http\HttpMethod;
use Raxos\Router\Contract\FrameInterface;
use function array_map;

/**
 * Class FrameStack
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.1.0
 */
final readonly class FrameStack implements DebuggableInterface
{

    public bool $isDynamic;

    /**
     * FrameStack constructor.
     *
     * @param HttpMethod $method
     * @param string $path
     * @param FrameInterface[] $frames
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public HttpMethod $method,
        public string $path,
        public array $frames
    )
    {
        $this->isDynamic = str_contains($this->path, '<');
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __debugInfo(): array
    {
        return [
            'route' => "{$this->method->name} {$this->path}",
            'stack' => array_map(\strval(...), $this->frames)
        ];
    }

}
