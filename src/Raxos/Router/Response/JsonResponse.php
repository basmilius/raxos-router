<?php
declare(strict_types=1);

namespace Raxos\Router\Response;

use function json_encode;
use const JSON_BIGINT_AS_STRING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * Class JsonResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Response
 * @since 1.0.0
 */
readonly class JsonResponse extends Response
{

    public const FLAGS = JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareBody(): string
    {
        return json_encode($this->value, self::FLAGS) ?: '{}';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function prepareHeaders(): void
    {
        if (!$this->hasHeader('Content-Type')) {
            $this->header('Content-Type', 'application/json');
        }
    }

}
