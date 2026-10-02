<?php
declare(strict_types=1);

use Raxos\Router\Definition\{ControllerClass};
use Raxos\Router\Mapper;
use RaxosTests\Router\{UnitController};

covers(ControllerClass::class);

it('round trips its complete definition through PHP serialization', function (): void {
    $value = Mapper::controller(UnitController::class);
    $copy = unserialize(serialize($value));
    expect($copy)->toBeInstanceOf(ControllerClass::class)->not->toBe($value);
    expect($copy->prefix)->toEqual($value->prefix);
    expect($copy->class)->toEqual($value->class);
    expect($copy->children)->toEqual($value->children);
    expect($copy->injectables)->toEqual($value->injectables);
    expect($copy->middlewares)->toEqual($value->middlewares);
    expect($copy->parameters)->toEqual($value->parameters);
    expect($copy->routes)->toEqual($value->routes);
    expect(serialize($copy))->toBe(serialize($value));
});
