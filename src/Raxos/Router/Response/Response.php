<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use JetBrains\PhpStorm\ExpectedValues;
use Raxos\Http\HttpCode;
use Raxos\Router\Router;
use function header;
use function http_response_code;
use function is_array;

/**
 * Class Response
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.0
 */
abstract class Response
{

    protected ?ResponseRegistry $responseRegistry;

    /**
     * Response constructor.
     *
     * @param Router $router
     * @param mixed $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        protected Router $router,
        protected mixed $value
    )
    {
        $this->responseRegistry = $router->getResponseRegistry();
    }

    /**
     * Gets the headers for the response.
     *
     * @return array
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getHeaders(): array
    {
        return $this->router->getResponseRegistry()->getHeaders();
    }

    /**
     * Returns TRUE if the given header exists on the response.
     *
     * @param string $name
     *
     * @return bool
     * @see ResponseRegistry::hasHeader()
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function hasHeader(string $name): bool
    {
        return $this->router->getResponseRegistry()->hasHeader($name);
    }

    /**
     * Adds a response header with the given name and content.
     *
     * @param string $name
     * @param string $content
     *
     * @return ResponseRegistry
     * @see ResponseRegistry::header()
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function header(string $name, string $content): ResponseRegistry
    {
        return $this->router->getResponseRegistry()->header($name, $content);
    }

    /**
     * Gets the http code for the response.
     *
     * @return int
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[ExpectedValues(valuesFromClass: HttpCode::class)]
    public final function getResponseCode(): int
    {
        return $this->router->getResponseRegistry()->getResponseCode();
    }

    /**
     * Gets the Router instance.
     *
     * @return Router
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Gets the value of the response.
     *
     * @return mixed
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Prepares the response body.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public abstract function prepareBody(): string;

    /**
     * Prepares the response headers.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public abstract function prepareHeaders(): void;

    /**
     * Sends the response to browser.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function respond(): void
    {
        $this->prepareHeaders();

        http_response_code($this->getResponseCode());

        $this->respondHeaders();
        $this->respondBody();
    }

    /**
     * Respond the body to the browser.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function respondBody(): void
    {
        echo $this->prepareBody();
    }

    /**
     * Respond the headers to the browser.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    private function respondHeaders(): void
    {
        foreach ($this->getHeaders() as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("{$name}: {$v}", replace: false);
                }
            } else {
                header("{$name}: {$value}");
            }
        }
    }

}
