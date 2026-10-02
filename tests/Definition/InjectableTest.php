<?php
declare(strict_types=1);

use Raxos\Router\Attribute\MapHeader;
use Raxos\Router\Definition\{DefaultValue, Injectable};

covers(Injectable::class);

it('round trips its complete definition through PHP serialization', function (): void {
    $value = new Injectable('value', ['string', 'int'], DefaultValue::of(0), new MapHeader('X-Value'));
    $copy = unserialize(serialize($value));
    expect($copy)->toBeInstanceOf(Injectable::class)->not->toBe($value);
    expect($copy->name)->toEqual($value->name);
    expect($copy->types)->toEqual($value->types);
    expect($copy->defaultValue)->toEqual($value->defaultValue);
    expect($copy->valueProvider)->toEqual($value->valueProvider);
    expect($copy->primaryType)->toEqual($value->primaryType);
    expect(serialize($copy))->toBe(serialize($value));
});
