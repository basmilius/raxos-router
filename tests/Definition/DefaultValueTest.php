<?php
declare(strict_types=1);

use Raxos\Router\Definition\DefaultValue;

covers(DefaultValue::class);

it('round trips its complete definition through PHP serialization', function (): void {
    $value = new DefaultValue(true, ['falsey' => 0, 'nullable' => null]);
    $copy = unserialize(serialize($value));
    expect($copy)->toBeInstanceOf(DefaultValue::class)->not->toBe($value);
    expect($copy->defined)->toEqual($value->defined);
    expect($copy->value)->toEqual($value->value);
    expect(serialize($copy))->toBe(serialize($value));
});

it('distinguishes an undefined default from explicit falsey defaults', function (mixed $value): void {
    expect(DefaultValue::none()->defined)->toBeFalse()->and(DefaultValue::of($value)->defined)->toBeTrue()->and(DefaultValue::of($value)->value)->toBe($value);
})->with([[null], [false], [0], ['']]);
