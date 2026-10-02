<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Frame\{ClosureFrame, FrameStack};

covers(FrameStack::class);

it('distinguishes dynamic routes and exposes useful debug information', function (): void {
    $frame = new ClosureFrame(static fn (): int => 1, []);
    $static = new FrameStack(HttpMethod::GET, '/items', '/items', [$frame]);
    $dynamic = new FrameStack(HttpMethod::POST, '/items/(?<id>\d+)', '/items/$id', [$frame]);
    expect($static->isDynamic)->toBeFalse()->and($dynamic->isDynamic)->toBeTrue()
        ->and($dynamic->__debugInfo())->toBe(['route' => 'POST /items/$id', 'stack' => [(string)$frame]]);
});
