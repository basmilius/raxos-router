<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\HttpResponseCode;
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Attribute\Child;
use Raxos\Router\Attribute\Controller;
use Raxos\Router\DynamicRouter;
use Raxos\Router\Mapper;
use Raxos\Router\Router;
use RaxosTests\Router\PreflightMiddleware;

it('resolves root routes in both router implementations', function (): void {
    $dynamic = new DynamicRouter();
    $dynamic->get('/', static fn(): HttpResponse => new NoContentHttpResponse());
    $compiled = Router::createFromMapping(null, $dynamic->dynamicRoutes, $dynamic->staticRoutes);
    expect($dynamic->resolve(HttpRequest::create(uri: '/'))->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
    expect($compiled->resolve(HttpRequest::create(uri: '/'))->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
});

it('keeps resolved routes scoped to the router instance', function (): void {
    $first = new DynamicRouter();
    $second = new DynamicRouter();
    $first->get('/review/$id', static fn(int $id): HttpResponse => new NoContentHttpResponse()->header('Id', (string)$id));
    $second->get('/review/$slug', static fn(string $slug): HttpResponse => new NoContentHttpResponse()->header('Slug', $slug));
    $first->resolve(HttpRequest::create(uri: '/review/7'));
    expect($second->resolve(HttpRequest::create(uri: '/review/7'))->headers->get('Slug'))->toBe('7');
});

it('invalidates route caches and compiles groups only after registration', function (): void {
    $router = new DynamicRouter();
    for ($i = 0; $i < 400; $i++) {
        $router->get('/route' . $i . '/$id', static fn(int $id): HttpResponse => new NoContentHttpResponse()->header('Id', (string)$id));
    }
    expect($router->combinedDynamicRegexes)->toBe([]);
    expect($router->resolve(HttpRequest::create(uri: '/route399/7'))->headers->get('Id'))->toBe('7');
    expect($router->combinedDynamicRegexes)->not->toBe([]);
    $router->get('/route399/$id', static fn(int $id): HttpResponse => new NoContentHttpResponse()->header('Id', 'updated'));
    expect($router->resolve(HttpRequest::create(uri: '/route399/7'))->headers->get('Id'))->toBe('updated');
});

it('runs preflight middleware without invoking the requested handler', function (): void {
    $calls = 0;
    $router = new DynamicRouter();
    $router->post('/action', #[PreflightMiddleware] function () use (&$calls): HttpResponse {
        $calls++;
        return new NoContentHttpResponse();
    });
    $request = HttpRequest::create(method: HttpMethod::OPTIONS, uri: '/action', headers: new HttpHeadersMap(['access-control-request-method' => ['POST']]));
    $response = $router->resolve($request);
    expect($calls)->toBe(0);
    expect($response->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
    expect($response->headers->get('Allow'))->toBe('POST');
    expect($response->headers->get('Access-Control-Allow-Origin'))->toBe('https://example.test');
});

it('honors explicit OPTIONS handlers and rejects unavailable preflight methods', function (): void {
    $router = new DynamicRouter();
    $router->options('/explicit', static fn(): HttpResponse => new NoContentHttpResponse()->header('Explicit', 'yes'));
    expect($router->resolve(HttpRequest::create(method: HttpMethod::OPTIONS, uri: '/explicit'))->headers->get('Explicit'))->toBe('yes');
    $router->get('/only-get', static fn(): HttpResponse => new NoContentHttpResponse());
    $request = HttpRequest::create(method: HttpMethod::OPTIONS, uri: '/only-get', headers: new HttpHeadersMap(['access-control-request-method' => ['POST']]));
    expect($router->resolve($request)->responseCode)->toBe(HttpResponseCode::METHOD_NOT_ALLOWED);
});

it('finds filtered attributes regardless of their original index', function (): void {
    $controller = new Controller('/review');
    expect(Mapper::attributeOf([new Child(stdClass::class), $controller], Controller::class))->toBe($controller);
});

it('returns 404 and 405 without invoking unrelated handlers', function (): void {
    $router = new DynamicRouter();
    $router->get('/known', static fn(): HttpResponse => new NoContentHttpResponse());
    expect($router->resolve(HttpRequest::create(uri: '/unknown'))->responseCode)->toBe(HttpResponseCode::NOT_FOUND)
        ->and($router->resolve(HttpRequest::create(uri: '/known', method: HttpMethod::POST))->responseCode)->toBe(HttpResponseCode::METHOD_NOT_ALLOWED);
});

it('injects typed parameters into compiled and dynamic routes', function (bool $compiled): void {
    $dynamic = new DynamicRouter();
    $dynamic->get('/items/$id/$active', static fn(int $id, bool $active): HttpResponse => new NoContentHttpResponse()->header('Value', $id . ':' . ($active ? 'yes' : 'no')));
    $router = $compiled ? Router::createFromMapping(null, $dynamic->dynamicRoutes, $dynamic->staticRoutes) : $dynamic;
    expect($router->resolve(HttpRequest::create(uri: '/items/0/false'))->headers->get('Value'))->toBe('0:no')
        ->and($router->resolve(HttpRequest::create(uri: '/items/7/true'))->headers->get('Value'))->toBe('7:yes')
        ->and($router->resolve(HttpRequest::create(uri: '/items/not-a-number/true'))->responseCode)->toBe(HttpResponseCode::NOT_FOUND);
})->with([false, true]);
