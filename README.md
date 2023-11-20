
# Ollama-Laravel Package

Ollama-Laravel is a Laravel package that provides a seamless integration with the [Ollama API](https://github.com/jmorganca/ollama). It includes functionalities for model management, prompt generation, format setting, and more. This package is perfect for developers looking to leverage the power of the Ollama API in their Laravel applications.

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

$response = Ollama::agent('You are a weather expert...')
    ->prompt('Why is the sky blue?')
    ->model('llama2')
    ->options(['temperature' => 0.8])
    ->stream(false)
    ->ask();
```

### Show Model Information

```php
$response = Ollama::model('Llama2')->show();
```

### Copy a Model

```php
Ollama::model('Llama2')->copy('newDestination');
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
