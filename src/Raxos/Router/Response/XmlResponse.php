<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use Exception;
use Raxos\Foundation\Util\XmlUtil;
use SimpleXMLElement;
use function is_array;

/**
 * Class XmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.0
 */
readonly class XmlResponse extends Response
{

    /**
     * {@inheritdoc}
     * @throws Exception
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareBody(): string
    {
        if ($this->value instanceof SimpleXMLElement) {
            $xml = $this->value;
        } else if (is_array($this->value)) {
            $xml = new SimpleXMLElement('<response/>');

            XmlUtil::arrayToXml($this->value, $xml);
        } else {
            $xml = new SimpleXMLElement('<response>' . ((string)$this->value) . '</response>');
        }

        return $xml->asXML() ?: '<response/>';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareHeaders(): void
    {
        if (!$this->hasHeader('Content-Type')) {
            $this->header('Content-Type', 'text/xml');
        }
    }

}
