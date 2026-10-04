<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos Router

Map PHP controller attributes to HTTP routes, with dependency injection and middleware.

[Documentation](https://raxos.dev/router/) | [Packagist](https://packagist.org/packages/raxos/router) | [Raxos](https://github.com/basmilius/raxos)

- Static and dynamic routes with typed path parameters.
- Query, header, request-model and relation mapping attributes.
- Nested controllers, validation, middleware and configurable responses.
- CORS preflight handling and mappings that can be reused across router instances.

## Installation

Requires PHP 8.5 or later. Enable the `ctype`, `fileinfo`, `json`, `simplexml` PHP extensions. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/router:^3.3"
```

## Usage

```php
<?php
declare(strict_types=1);

use Raxos\Container\Container;
use Raxos\Http\HttpMethod;
use Raxos\Http\HttpRequest;
use Raxos\Http\Response\JsonHttpResponse;
use Raxos\Router\Attribute\Controller;
use Raxos\Router\Attribute\Get;
use Raxos\Router\Router;

require __DIR__ . '/vendor/autoload.php';

#[Controller('/products')]
final readonly class ProductController
{
    #[Get('/$id')]
    public function show(int $id): JsonHttpResponse
    {
        return new JsonHttpResponse(['id' => $id]);
    }
}

$router = Router::createFromControllers(new Container(), [ProductController::class]);
$request = HttpRequest::create(method: HttpMethod::GET, uri: '/products/42');

$router->resolve($request)->send();
```

Path parameters use `$name`, matching the controller method parameter. Use `HttpRequest::createFromGlobals()` for incoming web requests. CORS preflight runs middleware and reports allowed methods without executing the target action; explicit OPTIONS handlers still run normally.

## Documentation

- [Routing basics](https://raxos.dev/router/routing-basics)
- [Parameter mapping](https://raxos.dev/router/parameter-mapping)
- [Middleware and validation](https://raxos.dev/router/middleware)
- [Building responses](https://raxos.dev/router/responses)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=router
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.

See [building route urls](https://raxos.dev/router/reverse-routing) for the optional APIs and their lifetime or transport guarantees.
