<?php

use Cloudstudio\Ollama\Ollama;
use Cloudstudio\Ollama\Services\ModelService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->ollama = new Ollama(new ModelService());

    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);
});

it('adds bearer token authentication', function () {
    Config::set('ollama-laravel.auth.type', 'bearer');
    Config::set('ollama-laravel.auth.token', 'test-token');

    $this->ollama->model('llama2')
        ->prompt('Test prompt')
        ->ask();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer test-token');
    });
});

it('adds basic authentication', function () {
    Config::set('ollama-laravel.auth.type', 'basic');
    Config::set('ollama-laravel.auth.username', 'user');
    Config::set('ollama-laravel.auth.password', 'pass');

    $this->ollama->model('llama2')
        ->prompt('Test prompt')
        ->ask();

    Http::assertSent(function ($request) {
        $expectedAuth = 'Basic ' . base64_encode('user:pass');
        return $request->hasHeader('Authorization', $expectedAuth);
    });
});

it('throws exception for missing bearer token', function () {
    Config::set('ollama-laravel.auth.type', 'bearer');
    Config::set('ollama-laravel.auth.token', null);

    expect(fn () => $this->ollama->model('llama2')
        ->prompt('Test prompt')
        ->ask()
    )->toThrow(
        InvalidArgumentException::class,
        'Bearer token is required when using token authentication'
    );
});

it('throws exception for missing basic auth credentials', function () {
    Config::set('ollama-laravel.auth.type', 'basic');
    Config::set('ollama-laravel.auth.username', null);
    Config::set('ollama-laravel.auth.password', null);

    expect(fn () => $this->ollama->model('llama2')
        ->prompt('Test prompt')
        ->ask()
    )->toThrow(
        InvalidArgumentException::class,
        'Username and password are required when using basic authentication'
    );
});

it('works without authentication', function () {
    Config::set('ollama-laravel.auth.type', null);

    $this->ollama->model('llama2')
        ->prompt('Test prompt')
        ->ask();

    Http::assertSent(function ($request) {
        return !$request->hasHeader('Authorization');
    });
});
