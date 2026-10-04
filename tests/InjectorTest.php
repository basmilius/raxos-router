<?php
declare(strict_types=1);

use Raxos\Container\Container;
use Raxos\Http\{HttpMethod, HttpRequest};
use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\{DynamicRouter, Injector, Runner};
use Raxos\Router\Error\{InvalidInjectionException, MissingInjectionException, ReflectionErrorException, UnexpectedException};
use Raxos\Router\Frame\FrameStack;
use RaxosTests\Router\{CountingProvider, InjectionTarget, PathState, PathValue};

covers(Injector::class);

it('converts supported scalar values', function (mixed $value, string $type, mixed $expected): void {
    expect(Injector::convertValue($value, $type))->toBe($expected);
})->with([
    [42, 'string', '42'], ['12', 'int', 12], ['12.75', 'int', 12], ['invalid', 'int', null],
    ['0', 'float', 0.0], ['1.25', 'float', 1.25], ['invalid', 'float', null],
    ['true', 'bool', true], ['1', 'bool', true], [true, 'bool', true], ['false', 'bool', false],
    ['0', 'bool', false], [false, 'bool', false], [2, 'bool', false], ['x', 'unknown', null]
]);

it('resolves values by provider, global, request and default priority', function (): void {
    $router = new DynamicRouter();
    $runner = new Runner($router, new FrameStack(HttpMethod::GET, '/', '/', []));
    $request = HttpRequest::create();
    $request->parameters->set('value', 'request');
    $router->globals->set('value', 'global');
    $provider = new CountingProvider();
    $parameter = new Injectable('value', ['string'], DefaultValue::of('default'), $provider);
    expect(Injector::getValue($runner, $request, $parameter, 'unit'))->toBe('provided')
        ->and(Injector::getValue($runner, $request, $parameter, 'unit'))->toBe('provided')
        ->and($provider->calls)->toBe(1);
    $request->parameters->unset('value:value');
    $parameter = new Injectable('value', ['string'], DefaultValue::of('default'), null);
    expect(Injector::getValue($runner, $request, $parameter, 'unit'))->toBe('global');
    $router->globals->unset('value');
    expect(Injector::getValue($runner, $request, $parameter, 'unit'))->toBe('request');
    $request->parameters->unset('value');
    expect(Injector::getValue($runner, $request, $parameter, 'unit'))->toBe('default');
});

it('caches a null provider result without calling the provider again', function (): void {
    $provider = new class implements Raxos\Contract\Router\ValueProviderInterface {
        public int $calls = 0;

        public function getRegex(Injectable $injectable): string
        {
            return '';
        }

        public function getValue(HttpRequest $request, Injectable $injectable): mixed
        {
            $this->calls++;

            return null;
        }
    };
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    $request = HttpRequest::create();
    $parameter = new Injectable('value', ['string'], DefaultValue::none(), $provider);
    expect(Injector::getValue($runner, $request, $parameter, 'unit'))->toBeNull()
        ->and(Injector::getValue($runner, $request, $parameter, 'unit'))->toBeNull()->and($provider->calls)->toBe(1);
});

it('resolves enums and string parsable values and keys parameter lists by name', function (): void {
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    $request = HttpRequest::create();
    $request->parameters->merge(['state' => 'active', 'code' => 'AB']);
    $values = Injector::getValues($runner, $request, [
        new Injectable('state', [PathState::class], DefaultValue::none(), null),
        new Injectable('code', [PathValue::class], DefaultValue::none(), null)
    ], 'unit');
    expect($values['state'])->toBe(PathState::ACTIVE)->and((string)$values['code'])->toBe('AB');
});

it('uses a container only after defaults have been considered', function (): void {
    $dependency = new stdClass();
    $container = new Container();
    $container->instance(stdClass::class, $dependency);
    $runner = new Runner(new DynamicRouter($container), new FrameStack(HttpMethod::GET, '/', '/', []));
    $request = HttpRequest::create();
    expect(Injector::getValue($runner, $request, new Injectable('value', [stdClass::class], DefaultValue::none(), null), 'unit'))->toBe($dependency)
        ->and(Injector::getValue($runner, $request, new Injectable('value', [stdClass::class], DefaultValue::of(null), null), 'unit'))->toBeNull();
    expect(fn() => Injector::getValue($runner, $request, new Injectable('missing', ['MissingUnitDependency'], DefaultValue::none(), null), 'unit'))->toThrow(UnexpectedException::class);
});

it('checks object compatibility and reports invalid and missing injections', function (): void {
    $runner = new Runner(new DynamicRouter(), new FrameStack(HttpMethod::GET, '/', '/', []));
    $request = HttpRequest::create();
    $request->parameters->set('value', new stdClass());
    $parameter = new Injectable('value', [PathValue::class], DefaultValue::none(), null);
    expect(Injector::isCorrectType(new PathValue('AB'), [Stringable::class]))->toBeTrue()
        ->and(Injector::isCorrectType('AB', ['string']))->toBeFalse()
        ->and(Injector::isCorrectType(new stdClass(), [PathValue::class]))->toBeFalse()
        ->and(fn() => Injector::getValue($runner, $request, $parameter, 'unit', 'handle'))->toThrow(InvalidInjectionException::class);
    $request->parameters->unset('value');
    expect(fn() => Injector::getValue($runner, $request, $parameter, 'unit'))->toThrow(MissingInjectionException::class);
});

it('injects uninitialized and private properties while preserving initialized values', function (): void {
    $target = new InjectionTarget();
    Injector::injectClassProperties($target, ['unset' => 'new', 'existing' => 'replace', 'nullable' => 'filled', 'private' => 42]);
    expect($target->unset)->toBe('new')->and($target->existing)->toBe('keep')
        ->and($target->nullable)->toBe('filled')->and($target->privateValue())->toBe(42);
    expect(fn() => Injector::injectClassProperties($target, ['missing' => 1]))->toThrow(ReflectionErrorException::class);
});
