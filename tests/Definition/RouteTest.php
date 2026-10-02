<?php
declare(strict_types=1);

use Raxos\Router\Definition\{Route};
use Raxos\Router\Mapper;
use RaxosTests\Router\{UnitController};

covers(Route::class);

it('round trips its complete definition through PHP serialization', function (): void {
    $value = Mapper::controller(UnitController::class)->routes[1];
    $copy = unserialize(serialize($value));
    expect($copy)->toBeInstanceOf(Route::class)->not->toBe($value);
    expect($copy->class)->toEqual($value->class);
    expect($copy->method)->toEqual($value->method);
    expect($copy->routes)->toEqual($value->routes);
    expect($copy->middlewares)->toEqual($value->middlewares);
    expect($copy->parameters)->toEqual($value->parameters);
    expect(serialize($copy))->toBe(serialize($value));
});
