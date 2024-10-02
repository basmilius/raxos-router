<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\Structure\HttpHeadersMap;

/**
 * Class BinaryResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class BinaryResponse extends Response
{

    /**
     * BinaryResponse constructor.
     *
     * @param string $data
     * @param HttpHeadersMap $headers
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $data,
        HttpHeadersMap $headers = new HttpHeadersMap()
    )
    {
        parent::__construct($headers);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function send(): void
    {
        parent::send();

        echo $this->data;
    }

}
