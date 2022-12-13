<?php
declare(strict_types=1);

namespace Raxos\Router\Route;

use Raxos\Router\Effect\{Effect, NotFoundEffect, ResponseEffect, ResultEffect};
use Raxos\Router\Error\RouterException;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function array_map;
use function count;

/**
 * Class RouteExecutor
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Route
 * @since 1.0.0
 */
readonly class RouteExecutor
{

    /** @var RouteFrame[] */
    private array $frames;

    /**
     * RouteExecutor constructor.
     *
     * @param array $frames
     * @param array $params
     * @param float $version
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        array $frames,
        private array $params,
        private float $version
    )
    {
        $frameCount = count($frames);
        $index = 0;

        $this->frames = array_map(fn(array $frame): RouteFrame => new RouteFrame($frame, $index === 0, ++$index === $frameCount), $frames);
    }

    /**
     * Executes the route.
     *
     * @param Router $router
     *
     * @return Effect
     * @throws RouterException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function execute(Router $router): Effect
    {
        $result = null;

        foreach ($this->frames as $frame) {
            if (!$frame->isVersionSatisfiable($this->version)) {
                return new NotFoundEffect($router);
            }

            $result = $frame->invoke($router, $this->params);

            if ($result instanceof Effect) {
                return $result;
            }

            if ($result instanceof Response) {
                return new ResponseEffect($router, $result);
            }
        }

        return new ResultEffect($router, $result);
    }

}
