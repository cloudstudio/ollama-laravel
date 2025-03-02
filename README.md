[![Latest Version on Packagist](https://img.shields.io/packagist/v/cloudstudio/ollama-laravel.svg?style=flat-square)](https://packagist.org/packages/cloudstudio/ollama-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/cloudstudio/ollama-laravel.svg?style=flat-square)](https://packagist.org/packages/cloudstudio/ollama-laravel)


# Ollama-Laravel Package[![Total Downloads](https://img.shields.io/packagist/dt/cloudstudio/ollama-laravel.svg?style=flat-square)](https://packagist.org/packages/cloudstudio/ollama-laravel)


Ollama-Laravel is a Laravel package that provides a seamless integration with the [Ollama API](https://github.com/jmorganca/ollama). It includes functionalities for model management, prompt generation, format setting, and more. This package is perfect for developers looking to leverage the power of the Ollama API in their Laravel applications.

## If you use laravel 10.x, please use the following version V1.0.9

```bash
https://github.com/cloudstudio/ollama-laravel/releases/tag/v1.0.9
```

## Installation

```bash
composer require cloudstudio/ollama-laravel
```

## Configuration

```bash
php artisan vendor:publish --tag="ollama-laravel-config"
```

Published config file:

```php
return [
    'model' => env('OLLAMA_MODEL', 'llama2'),
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello, how can I assist you today?'),
    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
];
```

## Usage

### Basic Usage

```php
use Cloudstudio\Ollama\Facades\Ollama;

/** @var array $response */
$response = Ollama::agent('You are a weather expert...')
    ->prompt('Why is the sky blue?')
    ->model('llama2')
    ->options(['temperature' => 0.8])
    ->stream(false)
    ->ask();
```


### Vision Support
    
```php
/** @var array $response */
$response = Ollama::model('llava:13b')
    ->prompt('What is in this picture?')
    ->image(public_path('images/example.jpg')) 
    ->ask();

// "The image features a close-up of a person's hand, wearing bright pink fingernail polish and blue nail polish. In addition to the colorful nails, the hand has two tattoos – one is a cross and the other is an eye."

```

### Chat Completion

```php
$messages = [
    ['role' => 'user', 'content' => 'My name is Toni Soriano and I live in Spain'],
    ['role' => 'assistant', 'content' => 'Nice to meet you , Toni Soriano'],
    ['role' => 'user', 'content' => 'where I live ?'],
];

$response = Ollama::agent('You know me really well!')
    ->model('llama2')
    ->chat($messages);

// "You mentioned that you live in Spain."

### Chat Completion

```
### Chat Completion with tools

```php
$messages = [
    ['role' => 'user', 'content' => 'What is the weather in Toronto?'],
];

$response = Ollama::model('llama3.1')
    ->tools([
        [
            "type"     => "function",
            "function" => [
                "name"        => "get_current_weather",
                "description" => "Get the current weather for a location",
                "parameters"  => [
                    "type"       => "object",
                    "properties" => [
                        "location" => [
                            "type"        => "string",
                            "description" => "The location to get the weather for, e.g. San Francisco, CA",
                        ],
                        "format"   => [
                            "type"        => "string",
                            "description" => "The format to return the weather in, e.g. 'celsius' or 'fahrenheit'",
                            "enum"        => ["celsius", "fahrenheit"],
                        ],
                    ],
                    "required"   => ["location", "format"],
                ],
            ],
        ],
    ])
    ->chat($messages);

```


### Streamable responses

```php

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Console\BufferedConsoleOutput;

/** @var \GuzzleHttp\Psr7\Response $response */
$response = Ollama::agent('You are a snarky friend with one-line responses')
    ->prompt("I didn't sleep much last night")
    ->model('llama3')
    ->options(['temperature' => 0.1])
    ->stream(true)
    ->ask();

$output = new BufferedConsoleOutput();
$responses = Ollama::processStream($response->getBody(), function($data) use ($output) {
    $output->write($data['response']);
});

$output->write("\n");
$complete = implode('', array_column($responses, 'response'));
$output->write("<info>$complete</info>");

```

### Show Model Information

```php
$response = Ollama::model('Llama2')->show();
```

### Copy a Model

```php
Ollama::model('Llama2')->copy('NewModel');
```

### Delete a Model

```php
Ollama::model('Llama2')->delete();
```

### Generate Embeddings

```php
$embeddings = Ollama::model('Llama2')->embeddings('Your prompt here');
```

## Testing

```bash
pest
```

## Changelog, Contributing, and Security

- [Changelog](CHANGELOG.md)
- [Contributing](CONTRIBUTING.md)

## Credits

- [Toni Soriano](https://github.com/cloudstudio)

## License

[MIT License](LICENSE.md)
