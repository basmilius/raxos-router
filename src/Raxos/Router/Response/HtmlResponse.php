<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

/**
 * Class HtmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.0
 */
class HtmlResponse extends Response
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareBody(): string
    {
        return (string)$this->value;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareHeaders(): void
    {
        if (!$this->hasHeader('Content-Type')) {
            $this->header('Content-Type', 'text/html');
        }
    }

}
