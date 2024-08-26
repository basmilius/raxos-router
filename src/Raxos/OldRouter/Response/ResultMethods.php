<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Response;

use Raxos\Http\HttpResponseCode;
use Raxos\OldRouter\Router;

/**
 * Trait ResultMethods
 *
 * @property Router $router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Response
 * @since 1.0.16
 */
trait ResultMethods
{

    /**
     * Respond with the given result.
     *
     * @template T
     *
     * @param T $result
     *
     * @return T
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected function with(mixed $result): mixed
    {
        return $result;
    }

    /**
     * Respond with the given header.
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected function withHeader(string $name, string $value, bool $replace = true): static
    {
        $this->router
            ->responseRegistry
            ->header($name, $value, $replace);

        return $this;
    }

    /**
     * Respond with the given headers.
     *
     * @param array<string, string> $headers
     * @param bool $replace
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected function withHeaders(array $headers, bool $replace = true): static
    {
        foreach ($headers as $name => $value) {
            $this->router
                ->responseRegistry
                ->header($name, $value, $replace);
        }

        return $this;
    }

    /**
     * Respond with the given response code.
     *
     * @param HttpResponseCode $responseCode
     *
     * @return $this
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected function withResponseCode(HttpResponseCode $responseCode): static
    {
        $this->router
            ->responseRegistry
            ->responseCode($responseCode);

        return $this;
    }

}
