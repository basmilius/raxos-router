<?php
declare(strict_types=1);

namespace Raxos\Router\Attribute;

use Attribute;
use Raxos\Contract\Database\DatabaseExceptionInterface;
use Raxos\Contract\Router\{AttributeInterface, ValueProviderInterface};
use Raxos\Database\Orm\Definition\RelationDefinition;
use Raxos\Database\Orm\Error\InvalidRelationException;
use Raxos\Database\Orm\Model;
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Error\{MissingInstanceException, UnexpectedException};
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
final readonly class MapModelRelation implements AttributeInterface, ValueProviderInterface
{

    /**
     * MapQuery constructor.
     *
     * @param string $parentInstanceName
     * @param string $relationKey
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function __construct(
        public string $parentInstanceName,
        public string $relationKey
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getRegex(Injectable $injectable): string
    {
        return RouterUtil::regex('\d+|\w{26}|\w{8}-\w{4}-\w{4}-\w{4}-\w{12}', $injectable->name, $injectable->defaultValue->defined);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.1.0
     */
    public function getValue(Request $request, Injectable $injectable): ?Model
    {
        try {
            /** @var Model $parentInstance */
            $parentInstance = $request->parameters->get("{$this->parentInstanceName}:value") ?? throw new MissingInstanceException($this->parentInstanceName);

            /** @var class-string<Model> $model */
            $model = $injectable->types[0];

            $primaryKey = $request->parameters->get($injectable->name);
            $property = $parentInstance->backbone->structure->getProperty($this->relationKey);

            if (!($property instanceof RelationDefinition)) {
                throw new InvalidRelationException($parentInstance::class, $property->name);
            }

            $relation = $parentInstance->backbone->structure->getRelation($property);

            return $relation
                ->query($parentInstance)
                ->wherePrimaryKey($model, $primaryKey)
                ->singleOrFail();
        } catch (DatabaseExceptionInterface $err) {
            throw new UnexpectedException($err, __METHOD__);
        }
    }

}
