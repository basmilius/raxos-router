<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Database\Error\DatabaseException;
use Raxos\Database\Orm\Model;
use Raxos\Router\Contract\ValueProviderInterface;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\RuntimeException;
use Raxos\Router\Request\Request;
use Raxos\Router\RouterUtil;

/**
 * Class MapQuery
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Router\Attribute
 * @since 1.1.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class MapModel implements AttributeInterface, ValueProviderInterface
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getRegex(Injectable $injectable): string
    {
        return RouterUtil::regex('\w{8}-\w{4}-\w{4}-\w{4}-\w{12}', $injectable->name, $injectable->defaultValue->defined);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getValue(Request $request, Injectable $injectable): ?Model
    {
        try {
            /** @var class-string<Model> $model */
            $model = $injectable->types[0];
            $primaryKey = $request->parameters->get($injectable->name);

            return $model::singleOrFail($primaryKey);
        } catch (DatabaseException $err) {
            throw RuntimeException::unexpected($err, __METHOD__);
        }
    }

}
