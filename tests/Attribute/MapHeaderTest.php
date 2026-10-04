<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Attribute\MapHeader;
use Raxos\Router\Definition\DefaultValue;
use Raxos\Router\Definition\Injectable;

covers(MapHeader::class);

it('reads headers without case sensitivity and uses an explicit fallback', function (): void {
    $provider = new MapHeader('X-Unit');
    $parameter = new Injectable('value', ['string'], DefaultValue::of('fallback'), $provider);
    expect($provider->getValue(HttpRequest::create(), $parameter))->toBe('fallback')
        ->and($provider->getValue(HttpRequest::create(headers: new HttpHeadersMap(['x-unit' => ['0']])), $parameter))->toBe('0')
        ->and($provider->getRegex($parameter))->toBe('?(?<value>[^/]+)?');
});
