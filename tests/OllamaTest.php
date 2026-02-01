<?php

use Cloudstudio\Ollama\Ollama;
use Cloudstudio\Ollama\Services\ModelService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->ollama = new Ollama(new ModelService());
});

it('will not use debugging functions', function () {
    expect(['dd', 'dump', 'ray'])->each->not->toBeUsed();
});

it('sets properties correctly and returns instance', function ($method, $value) {
    expect($this->ollama->$method($value))->toBeInstanceOf(Ollama::class);
})->with([
    'agent' => ['agent', 'Act as Bill Gates'],
    'prompt' => ['prompt', 'Who are you?'],
    'model' => ['model', 'llama2'],
    'format' => ['format', 'json'],
    'options' => ['options', ['temperature' => 0.7]],
    'stream' => ['stream', true],
    'raw' => ['raw', true],
    'think' => ['think', true],
]);

it('correctly handles format as string type', function () {
    $formatString = 'json';

    $ollama = $this->ollama->format($formatString);

    // Get the protected property value using reflection
    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('format');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBe($formatString);
    expect($ollama)->toBeInstanceOf(Ollama::class);
});

it('correctly handles format as array type', function () {
    $formatArray = [
        'type' => 'object',
        'properties' => [
            'search' => ['type' => 'string'],
            'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
        ],
        'required' => ['search', 'tags'],
        'additionalProperties' => false,
    ];

    $ollama = $this->ollama->format($formatArray);

    // Get the protected property value using reflection
    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('format');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBe($formatArray);
    expect($ollama)->toBeInstanceOf(Ollama::class);
});

it('maintains format value when chaining methods', function () {
    $formatArray = [
        'type' => 'object',
        'properties' => ['example' => ['type' => 'string']],
    ];

    $ollama = $this->ollama->format($formatArray)->model('gemma3:1b');

    // Get the protected property value using reflection
    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('format');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBe($formatArray);
    expect($ollama)->toBeInstanceOf(Ollama::class);
});

it('correctly processes ask method with real API call', function () {
    $response = $this->ollama->agent('You are a weather expert...')
        ->prompt('Why is the sky blue? answer only in 4 words')
        ->model('llama2')
        ->options(['temperature' => 0.8])
        ->stream(false)
        ->ask();

    expect($response)->toBeArray();
});

it('lists available local models', function () {
    $models = $this->ollama->models();
    expect($models)->toBeArray();
});

it('shows information about the selected model', function () {
    $models = $this->ollama->models();
    $model = $models['models'][0];
    $this->ollama->model($model['name']);
    $info = $this->ollama->show();
    expect($info)->toBeArray();
});

it('sends custom headers from config in HTTP requests', function () {
    // Set custom headers in config
    config(['ollama-laravel.headers' => [
        'Authorization' => 'Bearer test-key',
        'X-Custom-Header' => 'custom-value',
    ]]);

    // Fake HTTP and capture requests
    Http::fake();

    // Make a request to trigger the headers to be sent
    $this->ollama->prompt('test')->ask();

    // Test that the request contained the custom headers
    Http::assertSent(function ($request) {
        return
            $request->hasHeader('Authorization', 'Bearer test-key') &&
            $request->hasHeader('X-Custom-Header', 'custom-value');
    });
});

it('sets keepAlive correctly and returns instance', function () {
    expect($this->ollama->keepAlive('10m'))->toBeInstanceOf(Ollama::class);
});

it('allows keepAlive to be set to null', function () {
    $ollama = $this->ollama->keepAlive(null);

    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('keepAlive');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBeNull();
    expect($ollama)->toBeInstanceOf(Ollama::class);
});

it('uses config value for keepAlive by default', function () {
    config(['ollama-laravel.keep_alive' => '15m']);

    $ollama = new Ollama(new ModelService());

    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('keepAlive');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBe('15m');
});

it('defaults keepAlive to null when not configured', function () {
    config(['ollama-laravel.keep_alive' => null]);

    $ollama = new Ollama(new ModelService());

    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('keepAlive');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBeNull();
});

it('does not include keep_alive in request when null', function () {
    Http::fake();

    $this->ollama->keepAlive(null)->prompt('test')->ask();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        return !array_key_exists('keep_alive', $body);
    });
});

it('includes keep_alive in request when set', function () {
    Http::fake();

    $this->ollama->keepAlive('20m')->prompt('test')->ask();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        return isset($body['keep_alive']) && $body['keep_alive'] === '20m';
    });
});

it('sets think correctly with boolean and returns instance', function () {
    expect($this->ollama->think(true))->toBeInstanceOf(Ollama::class);
});

it('sets think correctly with string level and returns instance', function () {
    $ollama = $this->ollama->think('high');

    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('think');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBe('high');
    expect($ollama)->toBeInstanceOf(Ollama::class);
});

it('defaults think to true when called without arguments', function () {
    $ollama = $this->ollama->think();

    $reflection = new ReflectionClass($ollama);
    $property = $reflection->getProperty('think');
    $property->setAccessible(true);
    $value = $property->getValue($ollama);

    expect($value)->toBeTrue();
});

it('does not include think in request when null', function () {
    Http::fake();

    $this->ollama->prompt('test')->ask();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        return !array_key_exists('think', $body);
    });
});

it('includes think in ask request when set to true', function () {
    Http::fake();

    $this->ollama->think(true)->prompt('test')->ask();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        return isset($body['think']) && $body['think'] === true;
    });
});

it('includes think in chat request when set', function () {
    Http::fake();

    $this->ollama->think(true)->chat([['role' => 'user', 'content' => 'test']]);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        return isset($body['think']) && $body['think'] === true;
    });
});

it('includes think level in request when set to string', function () {
    Http::fake();

    $this->ollama->think('medium')->prompt('test')->ask();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        return isset($body['think']) && $body['think'] === 'medium';
    });
});
