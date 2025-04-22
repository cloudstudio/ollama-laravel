<?php

use Cloudstudio\Ollama\Ollama;
use Cloudstudio\Ollama\Services\ModelService;

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
