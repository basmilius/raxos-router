<?php
declare(strict_types=1);

namespace RaxosTests\Router;

use Attribute;
use Closure;
use Error;
use Raxos\Contract\Router\{AttributeInterface, MiddlewareInterface, ValueProviderInterface};
use Raxos\Foundation\Contract\StringParsableInterface;
use Raxos\Http\{HttpRequest, HttpResponse};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Router\Attribute\{Any, Child, Controller, Get, Injected, MapHeader, Post};
use Raxos\Router\Definition\Injectable;
use Raxos\Router\Responds;

enum PathState: string
{
    case ACTIVE = 'active';
    case SPECIAL = 'a.b+';
}

enum NumericState: int
{
    case FIRST = 1;
    case SECOND = 2;
}

final readonly class PathValue implements StringParsableInterface
{
    public function __construct(public string $value)
    {
    }

    public static function fromString(string $input): static
    {
        return new self($input);
    }

    public static function pattern(): string
    {
        return '[A-Z]{2}';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

#[Attribute(Attribute::TARGET_PARAMETER)]
final class CountingProvider implements AttributeInterface, ValueProviderInterface
{
    public int $calls = 0;

    public function getRegex(Injectable $injectable): string
    {
        return '(?<' . $injectable->name . '>custom)';
    }

    public function getValue(HttpRequest $request, Injectable $injectable): mixed
    {
        $this->calls++;
        return 'provided';
    }
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
final class UnitMiddleware implements MiddlewareInterface
{
    #[Injected]
    public string $marker;

    public function __construct(public string $label = 'default')
    {
    }

    public function handle(HttpRequest $request, Closure $next): HttpResponse
    {
        $trace = $request->parameters->get('trace') ?? [];
        $request->parameters->set('trace', [...$trace, $this->label . ':' . $this->marker]);
        return $next($request)->header('Middleware', $this->label);
    }
}

#[Controller('/units')]
#[Child(UnitChildController::class)]
#[UnitMiddleware('parent')]
final class UnitController
{
    #[Injected]
    public string $marker;

    #[Injected]
    private string $privateMarker;

    public function __construct(public int $seed = 7)
    {
    }

    #[Get('/')]
    public function index(): array
    {
        return ['seed' => $this->seed, 'marker' => $this->marker];
    }

    #[Get('/$id')]
    #[Post('/$id')]
    #[UnitMiddleware('route')]
    public function item(int $id, #[MapHeader('X-Unit')] string $header = 'fallback'): array
    {
        return ['id' => $id, 'header' => $header];
    }

    #[Any('/any')]
    public function any(): HttpResponse
    {
        return new NoContentHttpResponse();
    }

    public function helper(): string
    {
        return 'not a route';
    }
}

#[Controller('/child')]
final class UnitChildController
{
    #[Get('/')]
    public function index(): HttpResponse
    {
        return new NoContentHttpResponse();
    }
}

#[Controller]
final class RootUnitController
{
    #[Get]
    public function index(): HttpResponse
    {
        return new NoContentHttpResponse();
    }
}

final class UntypedUnitController
{
    #[Get]
    public function index($value): HttpResponse
    {
        return new NoContentHttpResponse();
    }
}

final class NoReturnUnitController
{
    #[Get]
    public function index()
    {
        return null;
    }
}

final class InjectionTarget
{
    public string $unset;
    public string $existing = 'keep';
    public ?string $nullable = null;
    private int $private;

    public function privateValue(): int
    {
        return $this->private;
    }
}

final class ResponseFactory
{
    use Responds {
        binary as public;
        error as public;
        file as public;
        forbidden as public;
        html as public;
        json as public;
        noContent as public;
        notFound as public;
        redirect as public;
        result as public;
        validationError as public;
        validationErrors as public;
    }
}
