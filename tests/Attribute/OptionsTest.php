<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Options;

covers(Options::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Options('/units');
    expect($attribute->method)->toBe(HttpMethod::OPTIONS)->and($attribute->path)->toBe('/units')->and(new Options()->path)->toBe('/');
    $flags = new ReflectionClass(Options::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
