<?php

namespace Cloudstudio\Ollama\Services;

use Cloudstudio\Ollama\Traits\MakesHttpRequests;
use GuzzleHttp\Exception\GuzzleException;

class ModelService
{

    use MakesHttpRequests;

    /**
     * Base URL for the API.
     */
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('ollama-laravel.url');
    }

    /**
     * List local models.
     *
     * @return array
     * @throws GuzzleException
     */
    public function listLocalModels(): array
    {
        return $this->sendRequest('/api/tags', [], 'get');
    }

    /**
     * Show information about a specific model.
     *
     * @param string $modelName
     * @return array
     * @throws GuzzleException
     */
    public function showModelInformation(string $modelName): array
    {
        return $this->sendRequest('/api/show', ['name' => $modelName]);
    }

    /**
     * Copy a model.
     *
     * @param string $source
     * @param string $destination
     * @return array
     * @throws GuzzleException
     */
    public function copyModel(string $source, string $destination): array
    {
        return $this->sendRequest('/api/copy', [
            'source' => $source,
            'destination' => $destination
        ]);
    }

    /**
     * Delete a model.
     *
     * @param string $modelName
     * @return array
     * @throws GuzzleException
     */
    public function deleteModel(string $modelName): array
    {
        return $this->sendRequest('/api/delete', ['name' => $modelName], 'delete');
    }

    /**
     * Pull a model.
     *
     * @param string $modelName
     * @return array
     * @throws GuzzleException
     */
    public function pullModel(string $modelName): array
    {
        return $this->sendRequest('/api/pull', ['name' => $modelName]);
    }

    /**
     * Generate embeddings from a model.
     *
     * @param string $modelName
     * @param string $prompt
     * @return array
     * @throws GuzzleException
     */
    public function generateEmbeddings(string $modelName, string $prompt): array
    {
        return $this->sendRequest('/api/embeddings', [
            'model' => $modelName,
            'prompt' => $prompt
        ]);
    }
}
