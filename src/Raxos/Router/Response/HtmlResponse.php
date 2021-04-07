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
    protected function respondBody(): void
    {
        echo $this->value;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondHeaders(): void
    {
        if (!$this->responseRegistry->hasHeader('Content-Type')) {
            $this->responseRegistry->header('Content-Type', 'text/html');
        }

        parent::respondHeaders();
    }

}
