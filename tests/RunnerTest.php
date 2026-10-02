<?php
declare(strict_types=1);

use Raxos\Http\{HttpMethod, HttpRequest, HttpResponseCode};
use Raxos\Router\{DynamicRouter, Runner};
use Raxos\Router\Error\{ControllerNotInstantiatedException, MissingInstanceException, UnexpectedException};
use Raxos\Router\Frame\{ClosureFrame, FrameStack};

covers(Runner::class);

it('returns a not found response for an empty stack', function (): void {
    $router = new DynamicRouter();
    $request = HttpRequest::create();
    $runner = new Runner($router, new FrameStack(HttpMethod::GET, '/', '/', []));
    expect($runner->run($request)->responseCode)->toBe(HttpResponseCode::NOT_FOUND)
        ->and($router->globals->get('request'))->toBe($request);
});

it('creates controller singletons once per runner', function (): void {
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    $calls = 0;
    $factory = function () use (&$calls): object {
        $calls++;
        return new stdClass();
    };
    $instance = $runner->singleton('unit', $factory);
    expect($runner->singleton('unit', $factory))->toBe($instance)->and($runner->singleton('unit'))->toBe($instance)
        ->and($calls)->toBe(1)->and($runner->controllers)->toBe(['unit' => $instance]);
    expect(fn () => $runner->singleton('missing'))->toThrow(ControllerNotInstantiatedException::class);
});

it('wraps unexpected failures with the active frame and preserves their cause', function (): void {
    $cause = new RuntimeException('unit failure');
    $frame = new ClosureFrame(static fn (): never => throw $cause, []);
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', [$frame]));
    try {
        $runner->run(HttpRequest::create());
        test()->fail('Expected a wrapped exception.');
    } catch (UnexpectedException $error) {
        expect($error->getPrevious())->toBe($cause)->and($error->call)->toBe((string)$frame);
    }
});

it('preserves router runtime exceptions unchanged', function (): void {
    $cause = new MissingInstanceException('unit');
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', [new ClosureFrame(static fn (): never => throw $cause, [])]));
    try {
        $runner->run(HttpRequest::create());
        test()->fail('Expected a router exception.');
    } catch (MissingInstanceException $error) {
        expect($error)->toBe($cause);
    }
});

it('does not invoke terminal handlers during preflight', function (): void {
    $calls = 0;
    $frame = new ClosureFrame(function () use (&$calls): int {
        return ++$calls;
    }, []);
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', [$frame]), ['GET', 'POST']);
    $response = $runner->run(HttpRequest::create(method: HttpMethod::OPTIONS));
    expect($response->responseCode)->toBe(HttpResponseCode::NO_CONTENT)
        ->and($response->headers->get('Allow'))->toBe('GET, POST')->and($calls)->toBe(0);
});
