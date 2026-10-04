<?php
declare(strict_types=1);

use Raxos\DateTime\DateTime;
use Raxos\Http\HttpMethod;
use Raxos\Http\HttpRequest;
use Raxos\Router\Error\InvalidRouteParametersException;
use Raxos\Router\Router;
use RaxosTests\Router\PathState;
use RaxosTests\Router\ReverseRoutingController;

covers(Router::class);

it('roundtrips encoded string segments without double decoding or changing segment boundaries', function (string $value): void {
    $router = Router::createFromControllers(null, [ReverseRoutingController::class]);
    $url = $router->url([ReverseRoutingController::class, 'text'], ['value' => $value], ['language' => 'nl nl', 'literal' => '+']);
    expect($url)->toContain('?language=nl%20nl&literal=%2B')
        ->and($router->resolve(HttpRequest::create(uri: $url))->result)->toBe(['value' => $value]);
})->with(['plain', 'a/b', 'a b', 'héllo 🌍', 'a%2Fb', 'a+b', 'a?b#c', '%252F']);

it('roundtrips DateTime serialization and enum values through typed route patterns', function (): void {
    $router = Router::createFromControllers(null, [ReverseRoutingController::class]);
    $date = DateTime::fromString('2026-01-02T03:04:05+02:00');
    $url = $router->url([ReverseRoutingController::class, 'date'], ['at' => $date]);
    expect($url)->toContain('T03%3A04%3A05%2B02%3A00')
        ->and($router->resolve(HttpRequest::create(uri: $url))->result)->toBe(['at' => $date->jsonSerialize()]);
    $url = $router->url([ReverseRoutingController::class, 'state'], ['state' => PathState::SPECIAL]);
    expect($router->resolve(HttpRequest::create(uri: $url))->result)->toBe(['state' => PathState::SPECIAL->value]);
});

it('omits optional segments and can select a specific method or template', function (): void {
    $router = Router::createFromControllers(null, [ReverseRoutingController::class]);
    $url = $router->url([ReverseRoutingController::class, 'optional']);
    expect($url)->toBe('/links/optional')->and($router->resolve(HttpRequest::create(uri: $url))->result)->toBe(['id' => 0]);
    expect($router->url([ReverseRoutingController::class, 'multiple'], ['id' => 42], method: HttpMethod::POST))->toBe('/links/second/42');
    expect($router->url([ReverseRoutingController::class, 'multiple'], ['id' => 42], template: '/links/first/$id'))->toBe('/links/first/42');
});

it('rejects missing extra and incorrectly typed route parameters', function (array $parameters): void {
    $router = Router::createFromControllers(null, [ReverseRoutingController::class]);
    expect(fn() => $router->url([ReverseRoutingController::class, 'multiple'], $parameters))->toThrow(InvalidRouteParametersException::class);
})->with([[[]], [['id' => 'abc']], [['id' => 1, 'extra' => 2]], [['id' => []]]]);
