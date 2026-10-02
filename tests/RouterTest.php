<?php
declare(strict_types=1);

use Raxos\Http\{HttpMethod, HttpRequest, HttpResponseCode};
use Raxos\Http\Response\ResultHttpResponse;
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Error\InvalidHandlerException;
use Raxos\Router\Router;
use RaxosTests\Router\{RootUnitController, UnitController};

covers(Router::class);

it('creates independent global state for compiled routers', function (): void {
    $first = new Router(null);
    $second = new Router(null);
    expect($first->globals->get('router'))->toBe($first)
        ->and($second->globals->get('router'))->toBe($second)
        ->and($first->resolve(HttpRequest::create(uri: '/missing'))->responseCode)->toBe(HttpResponseCode::NOT_FOUND);
    $first->globals->set('marker', 'first');
    expect($second->globals->has('marker'))->toBeFalse();
});

it('resolves an attributed root controller', function (): void {
    $router = Router::createFromControllers(null, [RootUnitController::class]);
    expect($router->resolve(HttpRequest::create(uri: '/'))->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
});

it('injects controller state and nested prefixes', function (): void {
    $router = Router::createFromControllers(null, [UnitController::class]);
    $router->globals->set('marker', 'injected');
    $response = $router->resolve(HttpRequest::create(uri: '/units'));
    expect($response)->toBeInstanceOf(ResultHttpResponse::class)
        ->and($response->result)->toBe(['seed' => 7, 'marker' => 'injected'])
        ->and($response->headers->get('Middleware'))->toBe('parent')
        ->and($router->resolve(HttpRequest::create(uri: '/units/child'))->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
});

it('runs controller and route middleware in registration order', function (): void {
    $router = Router::createFromControllers(null, [UnitController::class]);
    $router->globals->set('marker', 'unit');
    $request = HttpRequest::create(uri: '/units/42', headers: new HttpHeadersMap(['X-Unit' => ['custom']]));
    expect($router->resolve($request)->result)->toBe(['id' => 42, 'header' => 'custom'])
        ->and($request->parameters->get('trace'))->toBe(['parent:unit', 'route:unit']);
});

it('keeps values fresh when resolving a cached dynamic route', function (): void {
    $router = Router::createFromControllers(null, [UnitController::class]);
    $router->globals->set('marker', 'unit');
    foreach ([12, 12, 24] as $id) {
        expect($router->resolve(HttpRequest::create(uri: '/units/' . $id))->result['id'])->toBe($id);
    }
});

it('finds static and dynamic handlers and rejects unmapped methods', function (): void {
    $router = Router::createFromControllers(null, [UnitController::class]);
    expect($router->path([UnitController::class, 'index']))->toBe('/units')
        ->and($router->path([UnitController::class, 'item']))->toContain('/units/(?<id>');
    expect(fn () => $router->path([UnitController::class, 'helper']))->toThrow(InvalidHandlerException::class);
});

it('rejects malformed handler references', function (array $handler): void {
    expect(fn () => new Router(null)->path($handler))->toThrow(InvalidHandlerException::class);
})->with([[[]], [[UnitController::class]], [['UnknownController', 'index']], [[UnitController::class, 'missing']]]);

it('falls back to ANY for otherwise unsupported methods', function (): void {
    $router = Router::createFromControllers(null, [UnitController::class]);
    $router->globals->set('marker', 'unit');
    expect($router->resolve(HttpRequest::create(uri: '/units/any', method: HttpMethod::DELETE))->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
});
