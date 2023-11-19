<?php

namespace Cloudstudio\Ollama\Services;

use Cloudstudio\Ollama\Traits\MakesHttpRequests;

class ModelService
{

    use MakesHttpRequests;

    /**
     * Base URL for the API.
     */
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('ollama-laravel.url');
    }

    /**
     * List local models.
     *
     * @return array
     */
    public function listLocalModels()
    {
        return $this->sendRequest('/api/tags', [], 'get');
    }

    /**
     * Show information about a specific model.
     *
     * @param string $modelName
     * @return array
     */
    public function showModelInformation(string $modelName)
    {
        return $this->sendRequest('/api/show', ['name' => $modelName]);
    }

    /**
     * Copy a model.
     *
     * @param string $source
     * @param string $destination
     * @return array
     */
    public function copyModel(string $source, string $destination)
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
     */
    public function deleteModel(string $modelName)
    {
        return $this->sendRequest('/api/delete', ['name' => $modelName], 'delete');
    }

    /**
     * Pull a model.
     *
     * @param string $modelName
     * @return array
     */
    public function pullModel(string $modelName)
    {
        return $this->sendRequest('/api/pull', ['name' => $modelName]);
    }

    /**
     * Generate embeddings from a model.
     *
     * @param string $modelName
     * @param string $prompt
     * @return array
     */
    public function generateEmbeddings(string $modelName, string $prompt)
    {
        return $this->sendRequest('/api/embeddings', [
            'model' => $modelName,
            'prompt' => $prompt
        ]);
    }
}
