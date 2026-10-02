<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Get;

covers(Get::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Get('/units');
    expect($attribute->method)->toBe(HttpMethod::GET)->and($attribute->path)->toBe('/units')->and(new Get()->path)->toBe('/');
    $flags = new ReflectionClass(Get::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
