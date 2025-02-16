<?php

declare(strict_types=1);

namespace Cloudstudio\Ollama\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

trait MakesHttpRequests {

    /**
     * Get request headers including authentication and additional custom headers.
     *
     * @return array
     */
    protected function getHeaders(): array
    {
        $customHeaders = config('ollama-laravel.headers', []);

        $authHeaders = $this->getAuthenticationHeaders();

        return array_merge($authHeaders, $customHeaders);
    }

    /**
     * Retrieve the authentication headers based on the configured authentication type.
     *
     * @return array
     */
    protected function getAuthenticationHeaders(): array
    {
        $authType = config('ollama-laravel.auth.type');

        switch ($authType) {
            case 'bearer':
                return $this->getBearerHeader();
            case 'basic':
                return $this->getBasicHeader();
            default:
                return [];
        }
    }

    /**
     * Build the Bearer authentication header.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getBearerHeader(): array
    {
        $token = config('ollama-laravel.auth.token');
        if (!$token) {
            throw new InvalidArgumentException('Bearer token is required when using token authentication');
        }

        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Build the Basic authentication header.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getBasicHeader(): array
    {
        $username = config('ollama-laravel.auth.username');
        $password = config('ollama-laravel.auth.password');
        if (!$username || !$password) {
            throw new InvalidArgumentException('Username and password are required when using basic authentication');
        }
        $credentials = base64_encode($username . ':' . $password);

        return ['Authorization' => 'Basic ' . $credentials];
    }

    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return null|array|Response
     * @throws GuzzleException
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post'): null|array|Response
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
