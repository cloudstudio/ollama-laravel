<?php

namespace Cloudstudio\Ollama\Traits;

use Illuminate\Support\Facades\Http;

trait MakesHttpRequests
{
    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return array
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post')
    {
        $url = config('ollama-laravel.url') . $urlSuffix;
        $response = Http::timeout(config('ollama-laravel.connection.timeout'))->$method($url, $data);

        return $response->json();
    }
}
