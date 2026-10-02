<?php
declare(strict_types=1);

use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Router\Definition\{DefaultValue, Injectable, Middleware};
use Raxos\Router\{DynamicRouter, Runner};
use Raxos\Router\Error\{MissingInstanceException, UnexpectedException};
use Raxos\Router\Frame\{FrameStack, MiddlewareFrame};
use RaxosTests\Router\UnitMiddleware;

covers(MiddlewareFrame::class);

it('constructs middleware with arguments and injects its properties', function (): void {
    $router = new DynamicRouter();
    $router->globals->set('marker', 'injected');
    $frame = new MiddlewareFrame(new Middleware(UnitMiddleware::class, ['label'], [new Injectable('marker', ['string'], DefaultValue::none(), null)]));
    $request = HttpRequest::create();
    $response = $frame->handle(new Runner($router, new FrameStack(HttpMethod::GET, '/', '/', [])), $request, static fn () => new NoContentHttpResponse());
    expect($response->headers->get('Middleware'))->toBe('label')
        ->and($request->parameters->get('trace'))->toBe(['label:injected'])
        ->and((string)$frame)->toBe(UnitMiddleware::class . ' { $marker }');
});

it('wraps unexpected middleware errors and preserves router failures', function (bool $routerError): void {
    $error = $routerError ? new MissingInstanceException('unit') : new RuntimeException('unit');
    $frame = new MiddlewareFrame(new Middleware(UnitMiddleware::class, [], [new Injectable('marker', ['string'], DefaultValue::of('unit'), null)]));
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    try {
        $frame->handle($runner, HttpRequest::create(), static fn (): never => throw $error);
        test()->fail('Expected middleware failure.');
    } catch (Throwable $actual) {
        if ($routerError) {
            expect($actual)->toBe($error);
        } else {
            expect($actual)->toBeInstanceOf(UnexpectedException::class)->and($actual->getPrevious())->toBe($error)
                ->and($actual->call)->toBe((string)$frame);
        }
    }
})->with([false, true]);
