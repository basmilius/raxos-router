<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Contract\Http\Validate\ValidatorExceptionInterface;
use Raxos\Contract\Router\{AttributeInterface, ValueProviderInterface};
use Raxos\Http\HttpRequest;
use Raxos\Http\Validate\HttpClassValidator;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\ValidationFailedException;
use Raxos\Router\RouterUtil;

/**
 * Class ValidatedQuery
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 2.2.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ValidatedQuery implements AttributeInterface, ValueProviderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function getRegex(Injectable $injectable): string
    {
        return RouterUtil::convertPathParam($injectable->name, 'string', $injectable->defaultValue->defined);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function getValue(HttpRequest $request, Injectable $injectable): HttpRequestModelInterface
    {
        try {
            /** @var class-string<HttpRequestModelInterface> $model */
            $model = $injectable->types[0];

            $validator = new HttpClassValidator($model);
            $validator->validate($request->query->toArray());

            return $validator->get();
        } catch (ValidatorExceptionInterface $err) {
            throw new ValidationFailedException($err);
        }
    }

}
