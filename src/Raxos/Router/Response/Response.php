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
     * Sends the response to browser.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function respond(): void
    {
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
    protected abstract function respondBody(): void;

    /**
     * Respond the headers to the browser.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondHeaders(): void
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
