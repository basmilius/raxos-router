<?php
declare(strict_types=1);

namespace Raxos\Router\Frame;

use Closure;
use Raxos\Router\Contract\FrameInterface;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Injector;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{Response, ResultResponse};
use Raxos\Router\Runner;
use ReflectionException;
use ReflectionFunction;
use function call_user_func_array;
use function iterator_to_array;

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
    public function handle(Runner $runner, Request $request, Closure $next): Response
    {
        $parameters = Injector::getValues($runner, $request, $this->parameters, 'closure');
        $parameters = iterator_to_array($parameters);

        $response = call_user_func_array($this->closure, $parameters);

        if ($response instanceof Response) {
            return $response;
        }

        return new ResultResponse($response);
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
