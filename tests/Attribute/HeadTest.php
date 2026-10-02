<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Head;

covers(Head::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Head('/units');
    expect($attribute->method)->toBe(HttpMethod::HEAD)->and($attribute->path)->toBe('/units')->and(new Head()->path)->toBe('/');
    $flags = new ReflectionClass(Head::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
