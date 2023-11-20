<?php

namespace Cloudstudio\Ollama;

use Cloudstudio\Ollama\Services\ModelService;
use Cloudstudio\Ollama\Traits\MakesHttpRequests;

/**
 * Ollama class for integration with Laravel.
 */
class Ollama
{
    use MakesHttpRequests;

    protected $modelService;
    protected $selectedModel;

    protected $model;
    protected $prompt;
    protected $format;
    protected $options;
    protected $stream;
    protected $raw;
    protected $agent;

    /**
     * Ollama class constructor.
     */
    public function __construct(ModelService $modelService)
    {
        $this->modelService = $modelService;
        $this->model = config('ollama.model');
        $this->options = [];
    }

    /**
     * Sets the agent for generation.
     *
     * @param string $agent
     * @return $this
     */
    public function agent(string $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * Sets the prompt for generation.
     *
     * @param string $prompt
     * @return $this
     */
    public function prompt(string $prompt)
    {
        $this->prompt = $this->agent ? $this->agent . ' ' . $prompt : $prompt;
        return $this;
    }

    /**
     * Sets the model for subsequent operations.
     *
     * @param string $model
     * @return $this
     */
    public function model(string $model)
    {
        $this->selectedModel = $model;
        $this->model = $model;
        return $this;
    }

    /**
     * Sets the format for generation.
     *
     * @param string $format
     * @return $this
     */
    public function format(string $format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Sets additional options for generation.
     *
     * @param array $options
     * @return $this
     */
    public function options(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Sets whether to use streaming in the response.
     *
     * @param bool $stream
     * @return $this
     */
    public function stream(bool $stream)
    {
        $this->stream = $stream;
        return $this;
    }

    /**
     * Sets whether to return the response in raw format.
     *
     * @param bool $raw
     * @return $this
     */
    public function raw(bool $raw)
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * Lists available local models.
     *
     * @return array
     */
    public function models()
    {
        return $this->modelService->listLocalModels();
    }

    /**
     * Shows information about the selected model.
     *
     * @return array
     */
    public function show()
    {
        return $this->modelService->showModelInformation($this->selectedModel);
    }

    /**
     * Copies a model.
     *
     * @param string $destination
     * @return $this
     * @throws \Exception
     */
    public function copy(string $destination)
    {
        $this->modelService->copyModel($this->selectedModel, $destination);
        return $this;
    }

    /**
     * Deletes a model.
     *
     * @return $this
     * @throws \Exception
     */
    public function delete()
    {
        $this->modelService->deleteModel($this->selectedModel);
        return $this;
    }

    /**
     * Pulls a model.
     *
     * @return $this
     * @throws \Exception
     */
    public function pull()
    {
        $this->modelService->pullModel($this->selectedModel);
        return $this;
    }

    /**
     * Generates embeddings from the selected model.
     *
     * @param string $prompt
     * @return array
     * @throws \Exception
     */
    public function embeddings(string $prompt)
    {
        return $this->modelService->generateEmbeddings($this->selectedModel, $prompt);
    }

    /**
     * Generates content using the specified model.
     *
     * @return array
     */
    public function ask()
    {
        return $this->sendRequest('/api/generate', [
            'model' => $this->model,
            'prompt' => $this->prompt,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'raw' => $this->raw,
        ]);
    }
}
