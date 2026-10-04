<?php
declare(strict_types=1);

use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Router\{DynamicRouter, Mapper, Runner};
use Raxos\Router\Error\ControllerNotInstantiatedException;
use Raxos\Router\Frame\{FrameStack, RouteFrame};
use RaxosTests\Router\UnitController;

covers(RouteFrame::class);

it('invokes a registered controller with typed parameters', function (): void {
    $class = new ReflectionClass(UnitController::class);
    $frame = new RouteFrame(Mapper::route($class->getMethod('item'), $class));
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    $runner->singleton(UnitController::class, static fn() => new UnitController());
    $request = HttpRequest::create();
    $request->parameters->set('id', '42');
    expect($frame->handle($runner, $request, static fn() => throw new LogicException('Unexpected continuation'))->result)->toBe(['id' => 42, 'header' => 'fallback'])
        ->and((string)$frame)->toBe(UnitController::class . '->item($id, $header)');
});

it('returns responses directly and requires a controller instance', function (): void {
    $class = new ReflectionClass(UnitController::class);
    $frame = new RouteFrame(Mapper::route($class->getMethod('any'), $class));
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    expect(fn() => $frame->handle($runner, HttpRequest::create(), static fn() => null))->toThrow(ControllerNotInstantiatedException::class);
    $runner->singleton(UnitController::class, static fn() => new UnitController());
    expect($frame->handle($runner, HttpRequest::create(), static fn() => null))->toBeInstanceOf(NoContentHttpResponse::class);
});
