<?php
declare(strict_types=1);

namespace Raxos\OldRouter\Response;

use function json_encode;
use const JSON_BIGINT_AS_STRING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;

/**
 * Class JsonResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OldRouter\Response
 * @since 1.0.0
 */
readonly class JsonResponse extends Response
{

    public const int FLAGS = JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_THROW_ON_ERROR;

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