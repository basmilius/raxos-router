<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpQueryMap;
use Raxos\Router\Attribute\MapQuery;
use Raxos\Router\Definition\{DefaultValue, Injectable};
use RaxosTests\Router\{NumericState, PathState};

covers(MapQuery::class);

it('converts query scalars using a named key and fallback', function (string $type, string $value, mixed $expected): void {
    $provider = new MapQuery('query');
    $parameter = new Injectable('value', [$type], DefaultValue::of($expected), $provider);
    expect($provider->getValue(HttpRequest::create(uri: '/?query=' . urlencode($value)), $parameter))->toBe($expected)
        ->and($provider->getValue(HttpRequest::create(), $parameter))->toBe($expected);
})->with([['int', '0', 0], ['float', '1.25', 1.25], ['bool', 'false', false], ['string', '', '']]);

it('casts backed enums including numeric query strings and existing instances', function (): void {
    $provider = new MapQuery();
    $number = new Injectable('state', [NumericState::class], DefaultValue::none(), $provider);
    $string = new Injectable('state', [PathState::class], DefaultValue::none(), $provider);
    expect($provider->getValue(HttpRequest::create(uri: '/?state=2'), $number))->toBe(NumericState::SECOND)
        ->and($provider->getValue(HttpRequest::create(uri: '/?state=active'), $string))->toBe(PathState::ACTIVE)
        ->and($provider->getValue(HttpRequest::create(query: new HttpQueryMap(['state' => PathState::ACTIVE])), $string))->toBe(PathState::ACTIVE)
        ->and($provider->getValue(HttpRequest::create(uri: '/?state=invalid'), $string))->toBeNull();
});

it('normalizes array values and filters invalid enum elements', function (): void {
    $plain = new MapQuery();
    $parameter = new Injectable('state', ['array'], DefaultValue::none(), $plain);
    expect($plain->getValue(HttpRequest::create(), $parameter))->toBe([])
        ->and($plain->getValue(HttpRequest::create(uri: '/?state=active'), $parameter))->toBe(['active']);
    $provider = new MapQuery(enum: NumericState::class);
    expect($provider->getValue(HttpRequest::create(uri: '/?state[]=1&state[]=999&state[]=2'), $parameter))->toBe([NumericState::FIRST, NumericState::SECOND]);
});
