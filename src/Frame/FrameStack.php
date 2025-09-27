<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Raxos\Contract\DebuggableInterface;
use Raxos\Contract\Router\FrameInterface;
use Raxos\Http\HttpMethod;
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
     * @param string $pathPlain
     * @param FrameInterface[] $frames
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public HttpMethod $method,
        public string $path,
        public string $pathPlain,
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
            'route' => "{$this->method->name} {$this->pathPlain}",
            'stack' => array_map(\strval(...), $this->frames)
        ];
    }

}
