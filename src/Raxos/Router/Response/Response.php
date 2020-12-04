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

    /**
     * Response constructor.
     *
     * @param Router $router
     * @param array $headers
     * @param int $responseCode
     * @param mixed $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        protected Router $router,
        protected array $headers,
        #[ExpectedValues(valuesFromClass: HttpCode::class)] protected int $responseCode,
        protected mixed $value
    )
    {
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
        return $this->headers;
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
        http_response_code($this->responseCode);

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
        foreach ($this->headers as $name => $value) {
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
