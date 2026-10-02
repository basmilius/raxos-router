<?php
declare(strict_types=1);

use Raxos\Http\HttpResponseCode;
use Raxos\Http\Response\{BinaryHttpResponse, HtmlHttpResponse, JsonHttpResponse, ResultHttpResponse};
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Router\Responds;
use RaxosTests\Router\ResponseFactory;

covers(Responds::class);

it('constructs content responses with the supplied body, status and headers', function (): void {
    $factory = new ResponseFactory();
    $headers = new HttpHeadersMap(['X-Unit' => ['yes']]);
    $json = $factory->json(['unit' => 1], $headers, HttpResponseCode::CREATED);
    $html = $factory->html('<p>unit</p>');
    $binary = $factory->binary("\0\1");
    $result = $factory->result(['unit' => 1]);
    expect($json)->toBeInstanceOf(JsonHttpResponse::class)->and($json->body)->toBe(['unit' => 1])
        ->and($json->responseCode)->toBe(HttpResponseCode::CREATED)->and($json->headers)->toBe($headers)
        ->and($html)->toBeInstanceOf(HtmlHttpResponse::class)->and($html->body)->toBe('<p>unit</p>')
        ->and($binary)->toBeInstanceOf(BinaryHttpResponse::class)->and($binary->data)->toBe("\0\1")
        ->and($result)->toBeInstanceOf(ResultHttpResponse::class)->and($result->result)->toBe(['unit' => 1]);
});

it('constructs standard status and redirect responses', function (): void {
    $factory = new ResponseFactory();
    expect($factory->forbidden()->responseCode)->toBe(HttpResponseCode::FORBIDDEN)
        ->and($factory->notFound()->responseCode)->toBe(HttpResponseCode::NOT_FOUND)
        ->and($factory->noContent()->responseCode)->toBe(HttpResponseCode::NO_CONTENT)
        ->and($factory->redirect('/destination')->headers->get('Location'))->toBe('/destination')
        ->and($factory->redirect('/destination')->responseCode)->toBe(HttpResponseCode::FOUND);
});

it('serializes validation errors under the field name', function (): void {
    $factory = new ResponseFactory();
    $error = $factory->validationError('email', 'required', 'Required', ['field' => 'email'])->body;
    expect($error->errors)->toHaveKey('email')
        ->and($error->errors['email']->jsonSerialize()['error'])->toBe('http_validation_constraint_required');
    $errors = $factory->validationErrors(email: ['required' => 'Required'], name: ['length' => 'Too short'])->body;
    expect(array_keys($errors->errors))->toBe(['email', 'name']);
});
