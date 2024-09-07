<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Raxos\Http\{HttpHeader, HttpHeaders, HttpResponseCode};
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use function filemtime;
use function gmdate;
use function is_file;
use function md5_file;
use function mime_content_type;
use function readfile;
use function strtotime;

/**
 * Class FileResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.1.0
 */
final class FileResponse extends Response
{

    /**
     * FileResponse constructor.
     *
     * @param string $path
     * @param Request $request
     * @param HttpHeaders $headers
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $path,
        public Request $request,
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
        if (!is_file($this->path)) {
            throw RuntimeException::missingFile($this->path);
        }

        if (!$this->headers->has(HttpHeader::CONTENT_TYPE)) {
            $this->withHeader(HttpHeader::CONTENT_TYPE, mime_content_type($this->path));
        }

        $etag = md5_file($this->path);
        $modified = filemtime($this->path);

        $etagMatches = $this->request->headers->get(HttpHeader::IF_NONE_MATCH) === $etag;
        $modifiedMatches = $this->request->headers->get(HttpHeader::IF_MODIFIED_SINCE) === $modified;

        if ($etagMatches || $modifiedMatches) {
            $this->withResponseCode(HttpResponseCode::NOT_MODIFIED);
        } else {
            $this->withResponseCode(HttpResponseCode::OK);
        }

        $this->withHeader(HttpHeader::CACHE_CONTROL, 'public, max-age=31536000');
        $this->withHeader(HttpHeader::ETAG, $etag);
        $this->withHeader(HttpHeader::EXPIRES, gmdate('D, d M Y H:i:s \G\M\T', strtotime('+ 1 year')));
        $this->withHeader(HttpHeader::LAST_MODIFIED, gmdate('D, d M Y H:i:s \G\M\T', $modified));
        $this->withHeader(HttpHeader::PRAGMA, 'cache');

        parent::send();

        if ($this->responseCode === HttpResponseCode::OK) {
            readfile($this->path);
        }
    }

}
