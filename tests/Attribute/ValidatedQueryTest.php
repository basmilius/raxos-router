<?php
declare(strict_types=1);

use Raxos\Http\{HttpRequest};
use Raxos\Router\Attribute as A;
use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\Error\{ValidationFailedException};
use RaxosTests\Router as F;

covers(A\ValidatedQuery::class);

it('converts and validates query data into a typed request model', function (): void {
    $provider = new A\ValidatedQuery();
    $parameter = new Injectable('input', [F\UnitBodyInput::class], DefaultValue::none(), $provider);
    expect($provider->getValue(HttpRequest::create(uri: '/?quantity=2'), $parameter)->quantity)->toBe(2)
        ->and(preg_match('~^'.$provider->getRegex($parameter).'$~', 'input'))->toBe(1);
    expect(fn () => $provider->getValue(HttpRequest::create(uri: '/?quantity=0'), $parameter))->toThrow(ValidationFailedException::class);
    expect(fn () => $provider->getValue(HttpRequest::create(), $parameter))->toThrow(ValidationFailedException::class);
});
