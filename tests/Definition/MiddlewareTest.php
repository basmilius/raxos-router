<?php
declare(strict_types=1);

use Raxos\Router\Definition\{DefaultValue, Injectable, Middleware};
use RaxosTests\Router\{UnitMiddleware};

covers(Middleware::class);

it('round trips its complete definition through PHP serialization', function (): void {
    $value = new Middleware(UnitMiddleware::class, ['custom'], [new Injectable('marker', ['string'], DefaultValue::none(), null)]);
    $copy = unserialize(serialize($value));
    expect($copy)->toBeInstanceOf(Middleware::class)->not->toBe($value);
    expect($copy->class)->toEqual($value->class);
    expect($copy->arguments)->toEqual($value->arguments);
    expect($copy->injectables)->toEqual($value->injectables);
    expect(serialize($copy))->toBe(serialize($value));
});
