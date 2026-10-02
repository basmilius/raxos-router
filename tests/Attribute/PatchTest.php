<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Patch;

covers(Patch::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Patch('/units');
    expect($attribute->method)->toBe(HttpMethod::PATCH)->and($attribute->path)->toBe('/units')->and(new Patch()->path)->toBe('/');
    $flags = new ReflectionClass(Patch::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
