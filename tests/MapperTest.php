<?php
declare(strict_types=1);

use Raxos\Router\Attribute\{Controller, Get, MapHeader};
use Raxos\Router\Error\{InvalidReturnTypeException, MappingReflectionErrorException, MissingTypeException};
use Raxos\Router\Mapper;
use RaxosTests\Router\{InjectionTarget, NoReturnUnitController, UnitController, UnitMiddleware, UntypedUnitController};

covers(Mapper::class);

it('maps only attributed public routes and injected public properties', function (): void {
    $definition = Mapper::controller(UnitController::class);
    expect($definition->prefix)->toBe('/units')
        ->and(array_column($definition->routes, 'method'))->toBe(['index', 'item', 'any'])
        ->and(array_column($definition->injectables, 'name'))->toBe(['marker'])
        ->and($definition->parameters[0]->defaultValue->value)->toBe(7)
        ->and($definition->children)->toHaveCount(1)
        ->and(Mapper::controller(UnitController::class))->toBe($definition);
});

it('maps repeatable routes and value provider attributes', function (): void {
    $class = new ReflectionClass(UnitController::class);
    $route = Mapper::route($class->getMethod('item'), $class);
    expect($route->routes)->toHaveCount(2)
        ->and($route->parameters[1]->valueProvider)->toBeInstanceOf(MapHeader::class)
        ->and($route->parameters[1]->defaultValue->value)->toBe('fallback')
        ->and(Mapper::route($class->getMethod('item'), $class))->toBe($route);
});

it('groups dynamic routes and combines child prefixes', function (): void {
    [$dynamic, $static] = Mapper::for([UnitController::class]);
    expect($static)->toHaveKeys(['/units', '/units/child', '/units/any'])
        ->and($dynamic)->toHaveKey(2)
        ->and(array_values($dynamic[2])[0])->toHaveKeys(['segments', 'GET', 'POST']);
});

it('distinguishes absent, nullable and explicitly defined defaults', function (): void {
    $function = new ReflectionFunction(static fn (int $required, ?string $nullable, int $zero = 0): int => $required);
    $parameters = $function->getParameters();
    expect(Mapper::defaultValue($parameters[0])->defined)->toBeFalse()
        ->and(Mapper::defaultValue($parameters[1])->defined)->toBeTrue()
        ->and(Mapper::defaultValue($parameters[1])->value)->toBeNull()
        ->and(Mapper::defaultValue($parameters[2])->value)->toBe(0)
        ->and(Mapper::defaultValue(new ReflectionProperty(InjectionTarget::class, 'nullable'))->value)->toBeNull()
        ->and(Mapper::defaultValue(new ReflectionProperty(InjectionTarget::class, 'unset'))->defined)->toBeFalse();
});

it('filters router attributes while preserving their identity', function (): void {
    $first = new Get('/one');
    $second = new Get('/two');
    $controller = new Controller('/units');
    expect(array_values(Mapper::attributesOf([$first, $controller, $second], Get::class)))->toBe([$first, $second])
        ->and(Mapper::attributeOf([$first], Controller::class))->toBeNull();
});

it('keeps middleware definitions independent of previous attribute arguments', function (): void {
    $named = new ReflectionFunction(#[UnitMiddleware('custom')] static fn (): int => 1);
    $default = new ReflectionFunction(#[UnitMiddleware] static fn (): int => 1);
    $namedDefinition = Mapper::middleware($named->getAttributes()[0]);
    $defaultDefinition = Mapper::middleware($default->getAttributes()[0]);
    expect($namedDefinition->arguments)->toBe(['custom'])
        ->and($defaultDefinition->arguments)->toBe([])
        ->and(Mapper::middleware($default->getAttributes()[0]))->toBe($defaultDefinition);
});

it('reports an unknown controller as a mapping reflection error', function (): void {
    expect(fn () => Mapper::controller('RaxosTests\\Router\\MissingController'))->toThrow(MappingReflectionErrorException::class);
});

it('rejects untyped route parameters', function (): void {
    expect(fn () => Mapper::controller(UntypedUnitController::class))->toThrow(MissingTypeException::class);
});

it('rejects controller methods without return types', function (): void {
    expect(fn () => Mapper::controller(NoReturnUnitController::class))->toThrow(InvalidReturnTypeException::class);
});
