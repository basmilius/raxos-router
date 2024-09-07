<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpHeaders;

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
     * @param HttpHeaders $headers
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $data,
        HttpHeaders $headers = new HttpHeaders()
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
