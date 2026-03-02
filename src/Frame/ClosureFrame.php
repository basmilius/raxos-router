<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Contract\Router\FrameInterface;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\Response\ResultHttpResponse;
use Raxos\Router\{Injector, Runner};
use Raxos\Router\Definition\Injectable;
use ReflectionException;
use ReflectionFunction;

/**
 * Class ClosureFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Frame
 * @since 1.5.0
 */
final readonly class ClosureFrame implements FrameInterface
{

    /**
     * ClosureFrame constructor.
     *
     * @param Closure $closure
     * @param Injectable[] $parameters
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function __construct(
        public Closure $closure,
        public array $parameters
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function handle(Runner $runner, HttpRequest $request, Closure $next): HttpResponse
    {
        $parameters = Injector::getValues($runner, $request, $this->parameters, 'closure');

        $response = ($this->closure)(...$parameters);

        if ($response instanceof HttpResponse) {
            return $response;
        }

        return new ResultHttpResponse($response);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.5.0
     */
    public function __toString(): string
    {
        try {
            $reflector = new ReflectionFunction($this->closure);

            return $reflector->name;
        } catch (ReflectionException) {
            return 'Closure route';
        }
    }

}
