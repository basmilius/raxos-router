<?php
declare(strict_types=1);

use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\Error\{InvalidPathParameterException, TypeTooComplexException};
use Raxos\Router\RouterUtil;
use RaxosTests\Router\{CountingProvider, PathState, PathValue};

covers(RouterUtil::class);

it('normalizes a route path', function (string $path, string $expected): void {
    expect(RouterUtil::normalizePath($path))->toBe($expected);
})->with([['/', ''], ['', ''], ['items', '/items'], ['$id', '/$id'], ['/items/', '/items/'], ['(foo)', '(foo)']]);

it('splits paths without splitting regex groups', function (string $path, array $segments): void {
    expect(RouterUtil::pathToSegments($path))->toBe($segments);
})->with([['/', []], ['', []], ['/one//two/', ['one', 'two']], ['/one/(?<path>a/(b/c))/end', ['one', '(?<path>a/(b/c))', 'end']]]);

it('builds grouped patterns with stable route marks', function (): void {
    [$regex, $keys] = RouterUtil::buildGroupedRegex(['/a/(?<id>\d+)' => [], '/b/(?<id>\d+)' => []]);
    expect($keys)->toBe(['/a/(?<id>\d+)', '/b/(?<id>\d+)'])
        ->and(preg_match($regex, '/b/42', $matches))->toBe(1)
        ->and($matches['MARK'])->toBe('1')->and($matches['id'])->toBe('42')
        ->and(preg_match($regex, '/b/42/extra'))->toBe(0)
        ->and(array_keys(RouterUtil::buildGroupedRegexes([2 => ['/a/(?<id>\d+)' => []]])))->toBe([2]);
});

it('converts path variables with overlapping names', function (): void {
    $parameters = [new Injectable('id', ['int'], DefaultValue::none(), null), new Injectable('identifier', ['string'], DefaultValue::none(), null)];
    $regex = '#^' . RouterUtil::convertPath('/$identifier/$id', $parameters) . '$#';
    expect(preg_match($regex, '/hello/7', $matches))->toBe(1)
        ->and($matches['identifier'])->toBe('hello')->and($matches['id'])->toBe('7');
});

it('uses string parsable and custom provider patterns', function (): void {
    $parsed = new Injectable('value', [PathValue::class], DefaultValue::none(), null);
    $provided = new Injectable('value', ['object'], DefaultValue::none(), new CountingProvider());
    expect(RouterUtil::convertPath('/$value', [$parsed]))->toBe('/(?<value>[A-Z]{2})')
        ->and(RouterUtil::convertPath('/$value', [$provided]))->toBe('/(?<value>custom)')
        ->and(RouterUtil::convertPath('/constant', [$parsed]))->toBe('/constant');
});

it('treats backed enum values as literal path values', function (): void {
    $parameter = new Injectable('state', [PathState::class], DefaultValue::none(), null);
    $regex = '#^' . RouterUtil::convertPath('/$state', [$parameter]) . '$#';
    expect(preg_match($regex, '/a.b+'))->toBe(1)
        ->and(preg_match($regex, '/axb'))->toBe(0)
        ->and(preg_match($regex, '/unknown'))->toBe(0);
});

it('rejects types without path representations', function (): void {
    expect(fn () => RouterUtil::convertPath('/$value', [new Injectable('value', [stdClass::class], DefaultValue::none(), null)]))->toThrow(InvalidPathParameterException::class)
        ->and(fn () => RouterUtil::convertPathParam('value', 'array', false))->toThrow(TypeTooComplexException::class);
});

it('orders static paths before dynamic paths and shorter peers first', function (): void {
    expect(RouterUtil::routeSorter('/a', '/(?<id>\d+)'))->toBeLessThan(0)
        ->and(RouterUtil::routeSorter('/(?<id>\d+)', '/a'))->toBeGreaterThan(0)
        ->and(RouterUtil::routeSorter('/a', '/long'))->toBeLessThan(0)
        ->and(RouterUtil::routeSorter('/a', '/a'))->toBe(0);
});

it('extracts nullable and union reflection types', function (): void {
    $function = new ReflectionFunction(static fn (int|string|null $value): mixed => $value);
    expect(RouterUtil::types(null))->toBe([])
        ->and(RouterUtil::types($function->getParameters()[0]->getType()))->toBe(['string', 'int', 'null']);
});
