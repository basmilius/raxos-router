<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

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
class XmlResponse extends Response
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondBody(): void
    {
        if ($this->value instanceof SimpleXMLElement) {
            $xml = $this->value;
        } else if (is_array($this->value)) {
            $xml = new SimpleXMLElement('<response></response>');

            XmlUtil::arrayToXml($this->value, $xml);
        } else {
            $xml = new SimpleXMLElement('<response>' . ((string)$this->value) . '</response>');
        }

        echo $xml->asXML();
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function respondHeaders(): void
    {
        if (!array_key_exists('Content-Type', $this->headers)) {
            $this->headers['Content-Type'] = 'text/xml';
        }

        parent::respondHeaders();
    }

}
