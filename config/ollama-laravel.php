<?php

// Config for Cloudstudio/Ollama

return [
    'model' => env('OLLAMA_MODEL', 'llama2'),
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello, how can I assist you today?'),

    /*
    |--------------------------------------------------------------------------
    | Keep Alive Duration
    |--------------------------------------------------------------------------
    |
    | Controls how long models stay loaded in memory after a request.
    | Set to null to use the Ollama server's default configuration.
    | Examples: '5m' (5 minutes), '1h' (1 hour), '30s' (30 seconds)
    |
    */
    'keep_alive' => env('OLLAMA_KEEP_ALIVE', null),

    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
    'headers' => [
        'Authorization' => 'Bearer ' . env('OLLAMA_API_KEY'),
    ],
];
