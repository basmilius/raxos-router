<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Router\Attribute\Post;

covers(Post::class);

it('exposes the HTTP method and default or explicit path as a repeatable method attribute', function (): void {
    $attribute = new Post('/units');
    expect($attribute->method)->toBe(HttpMethod::POST)->and($attribute->path)->toBe('/units')->and(new Post()->path)->toBe('/');
    $flags = new ReflectionClass(Post::class)->getAttributes(Attribute::class)[0]->newInstance()->flags;
    expect($flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});
