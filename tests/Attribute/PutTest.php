<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Put;

covers(Put::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Put('/units');
    expect($attribute->method)->toBe(HttpMethod::PUT)->and($attribute->path)->toBe('/units')->and(new Put()->path)->toBe('/');
    $flags = new ReflectionClass(Put::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
