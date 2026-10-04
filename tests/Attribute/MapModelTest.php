<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Http\{HttpRequest};
use Raxos\Router\Attribute as A;
use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\Error\{UnexpectedException};
use RaxosTests\Router as F;
use function RaxosTests\Router\unitModels;

covers(A\MapModel::class);

beforeEach(fn() => unitModels());

it('maps primary keys, preserves identity and supports an absent optional model', function (): void {
    $provider = new A\MapModel();
    $parameter = new Injectable('model', [F\UnitModel::class], DefaultValue::none(), $provider);
    $request = HttpRequest::create(parameters: new Map(['model' => 1]));
    $model = $provider->getValue($request, $parameter);
    expect($model->name)->toBe('first')->and($provider->getValue($request, $parameter))->toBe($model)
        ->and(preg_match('~^' . $provider->getRegex($parameter) . '$~', '1'))->toBe(1);
    $optional = new Injectable('model', [F\UnitModel::class], DefaultValue::of(null), $provider);
    expect($provider->getValue(HttpRequest::create(), $optional))->toBeNull();
    expect(fn() => $provider->getValue(HttpRequest::create(parameters: new Map(['model' => 999])), $parameter))->toThrow(UnexpectedException::class);
});
