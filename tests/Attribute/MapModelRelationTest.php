<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Http\{HttpRequest};
use Raxos\Router\Attribute as A;
use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\Error\{MissingInstanceException, UnexpectedException};
use RaxosTests\Router as F;
use function RaxosTests\Router\unitModels;

covers(A\MapModelRelation::class);

beforeEach(fn () => unitModels());

it('resolves only children of the injected parent and wraps missing or invalid relations', function (): void {
    $provider = new A\MapModelRelation('parent', 'children');
    $parameter = new Injectable('child', [F\UnitChildModel::class], DefaultValue::none(), $provider);
    $request = HttpRequest::create(parameters: new Map(['parent:value' => F\UnitModel::singleOrFail(1), 'child' => 10]));
    expect($provider->getValue($request, $parameter)->id)->toBe(10)->and(preg_match('~^'.$provider->getRegex($parameter).'$~', '10'))->toBe(1);
    $request->parameters->set('child', 20);
    expect(fn () => $provider->getValue($request, $parameter))->toThrow(UnexpectedException::class);
    expect(fn () => $provider->getValue(HttpRequest::create(), $parameter))->toThrow(MissingInstanceException::class);
    expect(fn () => new A\MapModelRelation('parent', 'name')->getValue($request, $parameter))->toThrow(UnexpectedException::class);
});
