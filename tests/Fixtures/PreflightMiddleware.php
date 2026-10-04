<?php
declare(strict_types=1);

namespace RaxosTests\Router;

use Attribute;
use Closure;
use Raxos\Contract\Router\MiddlewareInterface;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;

#[Attribute(Attribute::TARGET_FUNCTION)]
final readonly class PreflightMiddleware implements MiddlewareInterface
{

    public function handle(HttpRequest $request, Closure $next): HttpResponse
    {
        return $next($request)->header('Access-Control-Allow-Origin', 'https://example.test');
    }

}
