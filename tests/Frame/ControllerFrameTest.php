<?php
declare(strict_types=1);

use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Router\{DynamicRouter, Mapper, Runner};
use Raxos\Router\Frame\{ControllerFrame, FrameStack};
use RaxosTests\Router\UnitController;

covers(ControllerFrame::class);

it('constructs a controller with defaults and injects properties before continuing', function (): void {
    $router = new DynamicRouter();
    $router->globals->set('marker', 'property');
    $frame = new ControllerFrame(Mapper::controller(UnitController::class));
    $runner = new Runner($router, new FrameStack(HttpMethod::GET, '/', '/', [$frame]));
    $request = HttpRequest::create();
    $response = new NoContentHttpResponse();
    $next = function (HttpRequest $actual) use ($request, $runner, $response): NoContentHttpResponse {
        expect($actual)->toBe($request)->and($runner->singleton(UnitController::class)->marker)->toBe('property');
        return $response;
    };
    expect($frame->handle($runner, $request, $next))->toBe($response)
        ->and($runner->singleton(UnitController::class)->seed)->toBe(7)
        ->and((string)$frame)->toBe(UnitController::class . '($seed) { $marker }');
    $instance = $runner->singleton(UnitController::class);
    $frame->handle($runner, $request, $next);
    expect($runner->singleton(UnitController::class))->toBe($instance);
});
