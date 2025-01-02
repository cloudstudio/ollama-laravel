<?php

namespace Cloudstudio\Ollama\Traits;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

trait MakesHttpRequests
{
    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return array|Response
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post')
    {
        $url = config('ollama-laravel.url') . $urlSuffix;

        if (!empty($data['stream']) && $data['stream'] === true) {
            $client = new Client();
            $response = $client->request($method, $url, [
                'json' => $data,
                'stream' => true,
                'timeout' => config('ollama-laravel.connection.timeout'),
            ]);

            return $response;
        } else {
            $response = Http::timeout(config('ollama-laravel.connection.timeout'))->$method($url, $data);
            return $response->json();
        }
    }
}
