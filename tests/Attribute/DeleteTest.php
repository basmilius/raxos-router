<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Delete;

covers(Delete::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Delete('/units');
    expect($attribute->method)->toBe(HttpMethod::DELETE)->and($attribute->path)->toBe('/units')->and(new Delete()->path)->toBe('/');
    $flags = new ReflectionClass(Delete::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
