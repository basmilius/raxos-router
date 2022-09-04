<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\HttpResponseCode;
use Raxos\Router\Router;
use function file_get_contents;
use function filemtime;
use function gmdate;
use function md5_file;
use function strtotime;

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
     * @param string $path
     * @param string $contentType
     * @param bool $allowCache
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        Router $router,
        public readonly string $path,
        public readonly string $contentType,
        public readonly bool $allowCache = true
    )
    {
        parent::__construct($router, $path);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareBody(): string
    {
        if ($this->getResponseCode() === HttpResponseCode::NOT_MODIFIED) {
            return '';
        }

        return file_get_contents($this->value);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareHeaders(): void
    {
        $this->header('Content-Type', $this->contentType);

        if (!$this->allowCache) {
            return;
        }

        $request = $this->router->getParameter('request');
        $headers = $request->headers();

        $etag = md5_file($this->value);
        $modified = filemtime($this->value);

        $etagMatch = $headers->has('if-none-match') && $headers->get('if-none-match') === $etag;
        $modifiedMatch = $headers->has('if-modified-since') && $headers->get('if-modified-since') === $modified;

        $this->header('Cache-Control', 'public, max-age=' . 3.1536E7);
        $this->header('Etag', $etag);
        $this->header('Expires', gmdate('D, d M Y H:i:s', strtotime('+ 1 year')) . ' GMT');
        $this->header('Last-Modified', gmdate('D, d M Y H:i:s', $modified) . ' GMT');
        $this->header('Pragma', 'cache');

        if ($etagMatch || $modifiedMatch) {
            $this->router->getResponseRegistry()->responseCode(HttpResponseCode::NOT_MODIFIED);
        }
    }

}
