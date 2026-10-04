<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Http\{HttpFile, HttpRequest};
use Raxos\Http\Structure\{HttpFilesMap, HttpHeadersMap, HttpPostMap};
use Raxos\Router\Attribute\Validated;
use Raxos\Router\Definition\{DefaultValue, Injectable};
use Raxos\Router\Error\{UnexpectedException, ValidationFailedException};
use RaxosTests\Router\{UnitBodyInput, UnitJsonRequest};

covers(Validated::class);

it('validates JSON bodies and form values into the same model', function (): void {
    $provider = new Validated();
    $parameter = new Injectable('input', [UnitBodyInput::class], DefaultValue::none(), $provider);
    $base = HttpRequest::create(headers: new HttpHeadersMap(['Content-Type' => ['application/json']]));
    $request = new UnitJsonRequest($base->cookies, $base->files, $base->headers, $base->post, $base->query, $base->server, $base->method, '/', '/', new Map(['fixture_body' => '{"quantity":2}']));
    expect($provider->getValue($request, $parameter)->quantity)->toBe(2)
        ->and($provider->getValue(HttpRequest::create(post: new HttpPostMap(['quantity' => '3'])), $parameter)->quantity)->toBe(3)
        ->and(preg_match('~^' . $provider->getRegex($parameter) . '$~', 'input'))->toBe(1);
    expect(fn() => $provider->getValue(HttpRequest::create(post: new HttpPostMap(['quantity' => 0])), $parameter))->toThrow(ValidationFailedException::class);
});

it('reads multipart JSON data, removes its pseudo-file and maps valid attachments', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'raxos-router-');
    try {
        file_put_contents($path, '{"quantity":4}');
        $data = new HttpFile(['error' => UPLOAD_ERR_OK, 'type' => 'application/json', 'name' => 'data.json', 'size' => 14, 'tmp_name' => $path]);
        $attachment = new HttpFile(['error' => UPLOAD_ERR_OK, 'type' => 'text/plain', 'name' => 'note.txt', 'size' => 1, 'tmp_name' => $path]);
        $failed = new HttpFile(['error' => UPLOAD_ERR_NO_FILE, 'type' => 'text/plain', 'name' => 'missing', 'size' => 0, 'tmp_name' => '']);
        $request = HttpRequest::create(files: new HttpFilesMap(['data' => [$data], 'attachment' => [$attachment, $failed]]));
        $provider = new Validated();
        $parameter = new Injectable('input', [UnitBodyInput::class], DefaultValue::none(), $provider);
        $input = $provider->getValue($request, $parameter);
        expect($input->quantity)->toBe(4)->and($input->attachment)->toBe($attachment)->and($request->files->has('data'))->toBeFalse();
        file_put_contents($path, 'broken');
        $request->files->set('data', [$data]);
        expect(fn() => $provider->getValue($request, $parameter))->toThrow(UnexpectedException::class);
    } finally {
        unlink($path);
    }
});
