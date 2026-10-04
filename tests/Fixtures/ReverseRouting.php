<?php
declare(strict_types=1);

namespace RaxosTests\Router;

use Raxos\DateTime\DateTime;
use Raxos\Router\Attribute\Controller;
use Raxos\Router\Attribute\Get;
use Raxos\Router\Attribute\Post;

#[Controller('/links')]
final class ReverseRoutingController
{
    #[Get('/text/$value')]
    public function text(string $value): array
    {
        return ['value' => $value];
    }

    #[Get('/date/$at')]
    public function date(DateTime $at): array
    {
        return ['at' => $at->jsonSerialize()];
    }

    #[Get('/enum/$state')]
    public function state(PathState $state): array
    {
        return ['state' => $state->value];
    }

    #[Get('/optional/$id')]
    public function optional(int $id = 0): array
    {
        return ['id' => $id];
    }

    #[Get('/first/$id')]
    #[Post('/second/$id')]
    public function multiple(int $id): array
    {
        return ['id' => $id];
    }
}
