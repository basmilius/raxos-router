<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

/**
 * Class HtmlResponse
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\Router\Response
 * @since 2.0.0
 */
class HtmlResponse extends Response
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondBody(): void
    {
        echo (string)$this->value;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondHeaders(): void
    {
        if (!array_key_exists('Content-Type', $this->headers)) {
            $this->headers['Content-Type'] = 'text/html';
        }

        parent::respondHeaders();
    }

}
