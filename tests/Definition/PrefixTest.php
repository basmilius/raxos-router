<?php
declare(strict_types=1);

use Raxos\Router\Definition\{Prefix};

covers(Prefix::class);

it('round trips its complete definition through PHP serialization', function (): void {
    $value = new Prefix('/$id', '/(?<id>\\d+)');
    $copy = unserialize(serialize($value));
    expect($copy)->toBeInstanceOf(Prefix::class)->not->toBe($value);
    expect($copy->plain)->toEqual($value->plain);
    expect($copy->regex)->toEqual($value->regex);
    expect(serialize($copy))->toBe(serialize($value));
});
