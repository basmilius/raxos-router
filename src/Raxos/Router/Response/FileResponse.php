<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpCode;
use Raxos\Http\HttpSendFile;
use Raxos\Router\Router;
use function header;
use function readfile;

/**
 * Class FileResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.0
 */
class FileResponse extends Response
{

    /**
     * FileResponse constructor.
     *
     * @param Router $router
     * @param string $value
     * @param string $contentType
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(Router $router, string $value, protected string $contentType)
    {
        parent::__construct($router, [], HttpCode::OK, $value);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondBody(): void
    {
        $request = $this->router->getParameter('request');
        $range = $request->headers()->get('range');

        if ($range !== null) {
            $sendFile = new HttpSendFile(
                $this->value,
                contentType: $this->contentType
            );

            $sendFile->handle($range);
        } else {
            header("Content-Type: {$this->contentType}");
            readfile($this->value);
        }
    }

}
