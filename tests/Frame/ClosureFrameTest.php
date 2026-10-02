<?php
declare(strict_types=1);

use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\{DynamicRouter, Runner};
use Raxos\Router\Frame\{ClosureFrame, FrameStack};

covers(ClosureFrame::class);

it('injects named parameters and wraps arbitrary results', function (): void {
    $router = new DynamicRouter();
    $router->globals->set('value', '42');
    $runner = new Runner($router, new FrameStack(HttpMethod::GET, '/', '/', []));
    $frame = new ClosureFrame(static fn (int $value): array => ['value' => $value], [new Injectable('value', ['int'], DefaultValue::none(), null)]);
    expect($frame->handle($runner, HttpRequest::create(), static fn () => throw new LogicException('Unexpected continuation'))->result)->toBe(['value' => 42])
        ->and((string)$frame)->toContain('{closure:');
});

it('returns an existing response unchanged', function (): void {
    $response = new NoContentHttpResponse();
    $frame = new ClosureFrame(static fn () => $response, []);
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    expect($frame->handle($runner, HttpRequest::create(), static fn () => throw new LogicException('Unexpected continuation')))->toBe($response);
});
