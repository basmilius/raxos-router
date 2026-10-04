<?php
declare(strict_types=1);

use Raxos\Http\HttpMethod;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\HttpResponseCode;
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Http\Response\ProblemDetails;
use Raxos\Router\DynamicRouter;

covers(DynamicRouter::class);

it('registers each HTTP convenience method', function (string $name, HttpMethod $method): void {
    $router = new DynamicRouter();
    $router->{$name}('/unit', static fn(): HttpResponse => new NoContentHttpResponse());
    expect($router->staticRoutes['/unit'][$method->name]->method)->toBe($method)
        ->and($router->resolve(HttpRequest::create(uri: '/unit', method: $method))->responseCode)->toBe(HttpResponseCode::NO_CONTENT);
})->with([
    ['get', HttpMethod::GET], ['post', HttpMethod::POST], ['put', HttpMethod::PUT],
    ['delete', HttpMethod::DELETE], ['patch', HttpMethod::PATCH], ['options', HttpMethod::OPTIONS], ['head', HttpMethod::HEAD]
]);

it('normalizes relative paths and preserves result values', function (): void {
    $router = new DynamicRouter();
    $router->get('unit', static fn(): array => ['ok' => true]);
    expect($router->resolve(HttpRequest::create(uri: '/unit'))->result)->toBe(['ok' => true]);
});

it('uses globals for dependencies without treating a static path as dynamic', function (): void {
    $router = new DynamicRouter();
    $router->globals->set('marker', 'injected');
    $router->get('/static', static fn(string $marker): string => $marker);
    expect($router->staticRoutes)->toHaveKey('/static')
        ->and($router->dynamicRoutes)->toBe([])
        ->and($router->resolve(HttpRequest::create(uri: '/static'))->result)->toBe('injected');
});

it('accepts invokable objects and method callables', function (): void {
    $router = new DynamicRouter();
    $handler = new class {
        public function __invoke(): string
        {
            return 'invoke';
        }

        public function method(): string
        {
            return 'method';
        }
    };
    $router->get('/invoke', $handler);
    $router->get('/method', [$handler, 'method']);
    expect($router->resolve(HttpRequest::create(uri: '/invoke'))->result)->toBe('invoke')
        ->and($router->resolve(HttpRequest::create(uri: '/method'))->result)->toBe('method');
});

it('prefers a static route over an overlapping dynamic route', function (): void {
    $router = new DynamicRouter();
    $router->get('/items/$name', static fn(string $name): string => $name);
    $router->get('/items/new', static fn(): string => 'static');
    expect($router->resolve(HttpRequest::create(uri: '/items/new'))->result)->toBe('static');
});

it('supports missing and supplied optional path values', function (): void {
    $router = new DynamicRouter();
    $router->get('/items/$id', static fn(int $id = 7): int => $id);
    expect($router->resolve(HttpRequest::create(uri: '/items'))->result)->toBe(7)
        ->and($router->resolve(HttpRequest::create(uri: '/items/12'))->result)->toBe(12);
});

it('keeps dynamic resolution valid after the bounded cache evicts entries', function (): void {
    $router = new DynamicRouter();
    $router->get('/items/$id', static fn(int $id): int => $id);

    for ($id = 0; $id < 1030; $id++) {
        expect($router->resolve(HttpRequest::create(uri: '/items/' . $id))->result)->toBe($id);
    }
    expect($router->resolve(HttpRequest::create(uri: '/items/0'))->result)->toBe(0);
});

it('dispatches an explicit problem response without exposing the exception message or cause', function (): void {
    $router = new DynamicRouter();
    $error = new RuntimeException('Internal database password', previous: new RuntimeException('Private connection details'));
    $router->get('/failed/$id', static fn(int $id): HttpResponse => ProblemDetails::fromException(
        $error,
        status: 503,
        detail: 'Please try again later.',
        instance: '/failed/' . $id
    )->response());

    $response = $router->resolve(HttpRequest::create(uri: '/failed/42'));
    $body = json_decode(json_encode($response->body, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);

    expect($response->responseCode)->toBe(HttpResponseCode::SERVICE_UNAVAILABLE)
        ->and($response->headers->get('Content-Type'))->toBe('application/problem+json')
        ->and($body)->toBe([
            'type' => 'about:blank',
            'title' => 'Service Unavailable',
            'status' => 503,
            'detail' => 'Please try again later.',
            'instance' => '/failed/42'
        ]);
});
