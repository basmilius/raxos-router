<?php
declare(strict_types=1);

namespace RaxosTests\Router;

use Raxos\Contract\Http\HttpRequestModelInterface;
use Raxos\Database\Connection\SQLite;
use Raxos\Database\Db;
use Raxos\Database\Orm\Attribute as ORM;
use Raxos\Database\Orm\{Model, ModelArrayList};
use Raxos\Http\{HttpFile, HttpRequest};
use Raxos\Http\Validate\Attribute\Property;
use Raxos\Http\Validate\Constraint\Min;

final readonly class UnitBodyInput implements HttpRequestModelInterface
{
    public function __construct(#[Property] #[Min(1)] public int $quantity, #[Property(optional: true)] public ?HttpFile $attachment = null) {}
}

final readonly class UnitJsonRequest extends HttpRequest
{
    public function body(): ?string
    {
        return $this->parameters->get('fixture_body');
    }
}

#[ORM\Table('router_units')]
final class UnitModel extends Model
{
    #[ORM\PrimaryKey]
    public int $id;
    #[ORM\Column]
    public string $name;
    #[ORM\HasMany(UnitChildModel::class, referenceKey: 'parent_id')]
    public ModelArrayList $children;
}

#[ORM\Table('router_children')]
final class UnitChildModel extends Model
{
    #[ORM\PrimaryKey]
    public int $id;
    #[ORM\Column]
    public int $parent_id;
}

function unitModels(): SQLite
{
    $connection = SQLite::createFromInMemory();
    $connection->connect();
    Db::register($connection);
    $connection->execute('CREATE TABLE router_units (id INTEGER PRIMARY KEY,name TEXT)');
    $connection->execute('CREATE TABLE router_children (id INTEGER PRIMARY KEY,parent_id INTEGER)');
    $connection->execute("INSERT INTO router_units VALUES (1,'first'),(2,'second')");
    $connection->execute('INSERT INTO router_children VALUES (10,1),(20,2)');

    return $connection;
}
