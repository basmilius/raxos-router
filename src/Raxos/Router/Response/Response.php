<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;
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
abstract readonly class Response implements ResponseInterface
{

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
        public Router $router,
        public mixed $value
    ) {}

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
        return $this->router->responseRegistry->getHeaders();
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
        return $this->router->responseRegistry->hasHeader($name);
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
        return $this->router->responseRegistry->header($name, $content);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getResponseCode(): HttpResponseCode
    {
        return $this->router->responseRegistry->getResponseCode();
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function respond(): void
    {
        $this->prepareHeaders();

        http_response_code($this->getResponseCode()->value);

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
        $body = $this->prepareBody();

        if ($body) {
            echo $body;
        }
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
