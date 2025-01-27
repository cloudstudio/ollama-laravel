<?php

declare(strict_types=1);

namespace Cloudstudio\Ollama\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

trait MakesHttpRequests
{
    /**
     * Get request headers including authentication if configured.
     */
    protected function getHeaders(): array
    {
        $headers = [];
        $authType = config('ollama-laravel.auth.type');

        if ($authType === 'bearer') {
            if (!config('ollama-laravel.auth.token')) {
                throw new InvalidArgumentException('Bearer token is required when using token authentication');
            }
            $headers['Authorization'] = 'Bearer ' . config('ollama-laravel.auth.token');
        } elseif ($authType === 'basic') {
            if (!config('ollama-laravel.auth.username') || !config('ollama-laravel.auth.password')) {
                throw new InvalidArgumentException('Username and password are required when using basic authentication');
            }
            $headers['Authorization'] = 'Basic ' . base64_encode(
                config('ollama-laravel.auth.username') . ':' . config('ollama-laravel.auth.password')
            );
        }

        return array_merge($headers, config('ollama-laravel.headers', []));
    }

    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return array|Response
     * @throws GuzzleException
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post'): array|Response
    {
        $url = config('ollama-laravel.url') . $urlSuffix;
        $headers = $this->getHeaders();

        if (!empty($data['stream']) && $data['stream'] === true) {
            $client = new Client();
            $options = [
                'json' => $data,
                'stream' => true,
                'timeout' => config('ollama-laravel.connection.timeout'),
                'headers' => $headers,
                'verify' => config('ollama-laravel.connection.verify_ssl', true),
            ];

            return $client->request($method, $url, $options);
        }

        $http = Http::withHeaders($headers)
            ->timeout(config('ollama-laravel.connection.timeout'))
            ->withOptions(['verify' => config('ollama-laravel.connection.verify_ssl', true)]);

        return $http->$method($url, $data)->json();
    }
}
