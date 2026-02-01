[![Latest Version on Packagist](https://img.shields.io/packagist/v/cloudstudio/ollama-laravel.svg?style=flat-square)](https://packagist.org/packages/cloudstudio/ollama-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/cloudstudio/ollama-laravel.svg?style=flat-square)](https://packagist.org/packages/cloudstudio/ollama-laravel)

# Ollama-Laravel Package

Ollama-Laravel is a Laravel package that provides seamless integration with the [Ollama API](https://github.com/jmorganca/ollama). It enables you to harness the power of local AI models for various tasks including text generation, vision analysis, chat completion, embeddings, and more. This package is perfect for developers looking to integrate AI capabilities into their Laravel applications without relying on external API services.

## 🌟 Features

- **Text Generation**: Generate content with customizable prompts and models
- **Vision Analysis**: Analyze images using multimodal models
- **Chat Completion**: Build conversational AI with message history
- **Thinking/Reasoning**: Access model reasoning with thinking models (Qwen 3, DeepSeek R1, etc.)
- **Function Calling**: Execute tools and functions through AI
- **Streaming Responses**: Real-time response streaming
- **Model Management**: List, copy, delete, and pull models
- **Embeddings**: Generate vector embeddings for semantic search
- **Flexible Configuration**: Customizable timeouts, temperature, and more

## 📋 Requirements

- PHP ^8.2
- Laravel ^11.0
- Ollama server running locally or remotely

## If you use Laravel 10.x, please use version V1.0.9

```bash
https://github.com/cloudstudio/ollama-laravel/releases/tag/v1.0.9
```

## 🚀 Installation

```bash
composer require cloudstudio/ollama-laravel
```

## ⚙️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="ollama-laravel-config"
```

Update your `.env` file:

```env
OLLAMA_MODEL=llama3.1
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_DEFAULT_PROMPT="Hello, how can I assist you today?"
OLLAMA_CONNECTION_TIMEOUT=300
```

Published config file:

```php
return [
    'model' => env('OLLAMA_MODEL', 'llama3.1'),
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello, how can I assist you today?'),
    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
];
```

## 📖 Usage Examples

### 🔧 Basic Text Generation

#### Simple Question Answering
```php
use Cloudstudio\Ollama\Facades\Ollama;

$response = Ollama::agent('You are a helpful assistant.')
    ->prompt('Explain quantum computing in simple terms')
    ->model('llama3.1')
    ->ask();

echo $response['response'];
```

#### Content Creation with Custom Options
```php
$response = Ollama::agent('You are a creative writing assistant.')
    ->prompt('Write a short story about a robot learning to paint')
    ->model('llama3.1')
    ->options([
        'temperature' => 0.8,  // More creative
        'top_p' => 0.9,
        'max_tokens' => 500
    ])
    ->ask();
```

#### Code Generation
```php
$response = Ollama::agent('You are an expert PHP developer.')
    ->prompt('Create a Laravel middleware that logs API requests with rate limiting')
    ->model('codellama')
    ->options(['temperature' => 0.2]) // Less creative for code
    ->ask();
```

### 🖼️ Vision Analysis

#### Basic Image Analysis
```php
$response = Ollama::model('llava:13b')
    ->prompt('Describe what you see in this image in detail')
    ->image(public_path('images/product-photo.jpg'))
    ->ask();

echo $response['response'];
// "This image shows a modern smartphone with a sleek black design..."
```

#### Product Catalog Analysis
```php
$response = Ollama::model('llava:13b')
    ->prompt('Extract product information from this image including brand, model, features, and estimated price range')
    ->image(storage_path('app/uploads/product.jpg'))
    ->ask();
```

#### Multiple Image Comparison
```php
$response = Ollama::model('llava:13b')
    ->prompt('Compare these images and identify the differences')
    ->images([
        public_path('images/before.jpg'),
        public_path('images/after.jpg')
    ])
    ->ask();
```

#### Document OCR and Analysis
```php
$response = Ollama::model('llava:13b')
    ->prompt('Extract all text from this document and summarize the key points')
    ->image(storage_path('app/documents/invoice.pdf'))
    ->ask();
```

### 💬 Chat Completion

#### Customer Support Bot
```php
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful customer support agent for an e-commerce website.'],
    ['role' => 'user', 'content' => 'I ordered a laptop 3 days ago but haven\'t received tracking information'],
    ['role' => 'assistant', 'content' => 'I understand your concern. Let me help you track your laptop order. Could you please provide your order number?'],
    ['role' => 'user', 'content' => 'My order number is ORD-12345']
];

$response = Ollama::model('llama3.1')
    ->chat($messages);
```

#### Educational Tutor
```php
$messages = [
    ['role' => 'system', 'content' => 'You are a patient math tutor helping a student learn calculus.'],
    ['role' => 'user', 'content' => 'I don\'t understand how to find the derivative of x^2 + 3x + 2'],
    ['role' => 'assistant', 'content' => 'I\'d be happy to help! Let\'s break this down step by step...'],
    ['role' => 'user', 'content' => 'Can you show me the power rule?']
];

$response = Ollama::model('llama3.1')
    ->options(['temperature' => 0.3]) // Lower temperature for educational content
    ->chat($messages);
```

#### Code Review Assistant
```php
$messages = [
    ['role' => 'system', 'content' => 'You are a senior software engineer providing code reviews.'],
    ['role' => 'user', 'content' => 'Please review this PHP function for potential improvements:'],
    ['role' => 'user', 'content' => '```php
function calculateTotal($items) {
    $total = 0;
    foreach($items as $item) {
        $total += $item["price"] * $item["quantity"];
    }
    return $total;
}
```']
];

$response = Ollama::model('codellama')
    ->chat($messages);
```

### 🧠 Thinking/Reasoning Models

Ollama supports thinking models like Qwen 3, DeepSeek R1, and others that can show their reasoning process.

#### Enable Thinking Output
```php
$response = Ollama::model('qwen3')
    ->prompt('What is the square root of 144 and why?')
    ->think()
    ->ask();

// Access the reasoning process
echo $response['thinking'];  // Model's step-by-step reasoning
echo $response['response'];  // Final answer
```

#### Thinking with Chat
```php
$messages = [
    ['role' => 'user', 'content' => 'Solve this step by step: If x + 5 = 12, what is x?']
];

$response = Ollama::model('deepseek-r1')
    ->think()
    ->chat($messages);

// Access thinking from chat response
echo $response['message']['thinking'];  // Reasoning steps
echo $response['message']['content'];   // Final answer
```

#### Thinking Levels (for supported models like GPT-OSS)
```php
// Some models support thinking levels: "low", "medium", "high"
$response = Ollama::model('gpt-oss')
    ->prompt('Explain quantum entanglement')
    ->think('high')  // Maximum reasoning depth
    ->ask();
```

#### Disable Thinking
```php
// Explicitly disable thinking output
$response = Ollama::model('qwen3')
    ->prompt('Quick answer: What is 2+2?')
    ->think(false)
    ->ask();
```

### 🔧 Function Calling / Tools

#### Weather Information System
```php
$messages = [
    ['role' => 'user', 'content' => 'What\'s the current weather in Tokyo and London?']
];

$response = Ollama::model('llama3.1')
    ->tools([
        [
            "type" => "function",
            "function" => [
                "name" => "get_current_weather",
                "description" => "Get the current weather for a specific location",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "location" => [
                            "type" => "string",
                            "description" => "The city and country, e.g. Tokyo, Japan",
                        ],
                        "unit" => [
                            "type" => "string",
                            "description" => "Temperature unit",
                            "enum" => ["celsius", "fahrenheit"],
                        ],
                    ],
                    "required" => ["location"],
                ],
            ],
        ]
    ])
    ->chat($messages);
```

#### Database Query Assistant
```php
$tools = [
    [
        "type" => "function",
        "function" => [
            "name" => "execute_sql_query",
            "description" => "Execute a read-only SQL query on the database",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "query" => [
                        "type" => "string",
                        "description" => "The SQL SELECT query to execute",
                    ],
                    "table" => [
                        "type" => "string",
                        "description" => "The primary table being queried",
                    ]
                ],
                "required" => ["query", "table"],
            ],
        ],
    ]
];

$messages = [
    ['role' => 'user', 'content' => 'Show me the top 5 customers by total orders']
];

$response = Ollama::model('llama3.1')
    ->tools($tools)
    ->chat($messages);
```

### 🌊 Streaming Responses

#### Real-time Content Generation
```php
use Illuminate\Console\BufferedConsoleOutput;

$response = Ollama::agent('You are a creative storyteller.')
    ->prompt('Write an engaging short story about time travel')
    ->model('llama3.1')
    ->options(['temperature' => 0.8])
    ->stream(true)
    ->ask();

$output = new BufferedConsoleOutput();
$responses = Ollama::processStream($response->getBody(), function($data) use ($output) {
    echo $data['response']; // Output in real-time
    flush();
});

$complete = implode('', array_column($responses, 'response'));
```

#### Live Chat Implementation
```php
// In your controller
public function streamChat(Request $request)
{
    $response = Ollama::agent('You are a helpful assistant.')
        ->prompt($request->input('message'))
        ->model('llama3.1')
        ->stream(true)
        ->ask();

    return response()->stream(function() use ($response) {
        Ollama::processStream($response->getBody(), function($data) {
            echo "data: " . json_encode($data) . "\n\n";
            flush();
        });
    }, 200, [
        'Content-Type' => 'text/plain',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no'
    ]);
}
```

### 📊 Embeddings for Semantic Search

#### Document Similarity Search
```php
// Generate embeddings for documents
$documents = [
    'Laravel is a PHP web framework',
    'Python is a programming language',
    'React is a JavaScript library'
];

$embeddings = [];
foreach ($documents as $doc) {
    $embeddings[] = Ollama::model('nomic-embed-text')
        ->embeddings($doc);
}

// Search for similar content
$query = 'Web development framework';
$queryEmbedding = Ollama::model('nomic-embed-text')
    ->embeddings($query);

// Calculate cosine similarity (implement your similarity function)
$similarities = calculateCosineSimilarity($queryEmbedding, $embeddings);
```

#### Product Recommendation System
```php
// Generate product embeddings
$productDescription = 'Wireless noise-canceling headphones with 30-hour battery life';
$productEmbedding = Ollama::model('nomic-embed-text')
    ->embeddings($productDescription);

// Store embedding in database for later similarity searches
DB::table('products')->where('id', $productId)->update([
    'embedding' => json_encode($productEmbedding['embedding'])
]);
```

## 🛠️ Model Management

### List Available Models
```php
$models = Ollama::models();
foreach ($models['models'] as $model) {
    echo "Model: " . $model['name'] . " (Size: " . $model['size'] . ")\n";
}
```

### Get Model Information
```php
$info = Ollama::model('llama3.1')->show();
echo "Model: " . $info['details']['family'] . "\n";
echo "Parameters: " . $info['details']['parameter_size'] . "\n";
```

### Copy and Manage Models
```php
// Copy a model
Ollama::model('llama3.1')->copy('my-custom-llama');

// Pull a new model
Ollama::model('codellama:7b')->pull();

// Delete a model
Ollama::model('old-model')->delete();
```

## 🏗️ Advanced Use Cases

### Content Moderation System
```php
class ContentModerationService
{
    public function moderateContent(string $content): array
    {
        $response = Ollama::agent(
            'You are a content moderator. Analyze content for inappropriate material, spam, or policy violations. Respond with JSON containing "safe" (boolean), "categories" (array), and "confidence" (0-1).'
        )
        ->prompt("Analyze this content: {$content}")
        ->model('llama3.1')
        ->format('json')
        ->options(['temperature' => 0.1])
        ->ask();

        return json_decode($response['response'], true);
    }
}
```

### Automated Code Documentation
```php
class CodeDocumentationService
{
    public function generateDocumentation(string $code): string
    {
        return Ollama::agent(
            'You are a technical writer. Generate comprehensive PHPDoc comments for the given code.'
        )
        ->prompt("Generate documentation for this code:\n\n{$code}")
        ->model('codellama')
        ->options(['temperature' => 0.2])
        ->ask()['response'];
    }
}
```

### Multi-language Translation Service
```php
class TranslationService
{
    public function translate(string $text, string $fromLang, string $toLang): string
    {
        return Ollama::agent(
            "You are a professional translator. Translate the given text accurately while preserving tone and context."
        )
        ->prompt("Translate from {$fromLang} to {$toLang}: {$text}")
        ->model('llama3.1')
        ->options(['temperature' => 0.3])
        ->ask()['response'];
    }
}
```

### Data Analysis Assistant
```php
class DataAnalysisService
{
    public function analyzeCSV(string $csvPath): array
    {
        $csvContent = file_get_contents($csvPath);
        
        $response = Ollama::agent(
            'You are a data analyst. Analyze the CSV data and provide insights, trends, and recommendations in JSON format.'
        )
        ->prompt("Analyze this CSV data:\n\n{$csvContent}")
        ->model('llama3.1')
        ->format('json')
        ->ask();

        return json_decode($response['response'], true);
    }
}
```

## 🎯 Best Practices

### 1. Model Selection
- Use `llama3.1` for general tasks and reasoning
- Use `codellama` for code-related tasks
- Use `llava` models for vision tasks
- Use `nomic-embed-text` for embeddings

### 2. Temperature Settings
- **0.1-0.3**: Factual, deterministic outputs (code, analysis)
- **0.4-0.7**: Balanced creativity and accuracy
- **0.8-1.0**: Creative writing, brainstorming

### 3. Prompt Engineering
```php
// Good: Specific and detailed
$response = Ollama::agent('You are a senior Laravel developer with 10 years of experience.')
    ->prompt('Create a secure user authentication system using Laravel Sanctum with rate limiting and email verification')
    ->ask();

// Better: Include context and constraints
$response = Ollama::agent('You are a senior Laravel developer. Follow PSR-12 coding standards and include comprehensive error handling.')
    ->prompt('Create a user authentication system with these requirements: Laravel Sanctum, rate limiting (5 attempts per minute), email verification, and password reset functionality. Include middleware and proper validation.')
    ->ask();
```

### 4. Error Handling
```php
try {
    $response = Ollama::model('llama3.1')
        ->prompt('Your prompt here')
        ->ask();
} catch (\Exception $e) {
    Log::error('Ollama request failed: ' . $e->getMessage());
    // Handle gracefully
}
```

### 5. Performance Optimization
```php
// Use keepAlive for multiple requests
$ollama = Ollama::model('llama3.1')->keepAlive('10m');

// Process multiple prompts
foreach ($prompts as $prompt) {
    $response = $ollama->prompt($prompt)->ask();
    // Process response
}
```

## 🧪 Testing

Run the test suite:

```bash
pest
```

Example test for your AI service:

```php
it('can generate content with Ollama', function () {
    $response = Ollama::agent('You are a test assistant.')
        ->prompt('Say hello')
        ->model('llama3.1')
        ->ask();
    
    expect($response)->toHaveKey('response');
    expect($response['response'])->toBeString();
});
```

## 📝 Changelog, Contributing, and Security

- [Changelog](CHANGELOG.md)
- [Contributing](CONTRIBUTING.md)

## 🎖️ Credits

- [Toni Soriano](https://github.com/cloudstudio)

## 📄 License

[MIT License](LICENSE.md)
