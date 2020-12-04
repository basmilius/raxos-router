<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;
use Raxos\Http\HttpMethods;

/**
 * Class Route
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{

    /**
     * Route constructor.
     *
     * @param string $path
     * @param string $method
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(protected string $path, #[ExpectedValues(valuesFromClass: HttpMethods::class)] protected string $method = HttpMethods::ANY)
    {
    }

    /**
     * Gets the request method.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[ExpectedValues(valuesFromClass: HttpMethods::class)]
    public final function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Gets the request path.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getPath(): string
    {
        return $this->path;
    }

}
