<?php

namespace Cloudstudio\Ollama;

use Closure;
use Cloudstudio\Ollama\Services\ModelService;
use Cloudstudio\Ollama\Traits\MakesHttpRequests;
use Cloudstudio\Ollama\Traits\StreamHelper;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Exception;

/**
 * Ollama class for integration with Laravel.
 */
class Ollama {

    use MakesHttpRequests;
    use StreamHelper;

    /**
     * modelService
     *
     * @var ModelService
     */
    protected ModelService $modelService;

    /**
     * selectedModel
     *
     * @var string
     */
    protected string $selectedModel;

    /**
     * model
     *
     * @var string
     */
    protected string $model;

    /**
     * prompt
     *
     * @var null | string
     */
    protected ?string $prompt = null;

    /**
     * format
     *
     * @var null | string
     */
    protected ?string $format = null;

    /**
     * options
     *
     * @var null | array
     */
    protected ?array $options = null;

    /**
     * options
     *
     * @var null | array
     */
    protected ?array $tools = null;

    /**
     * stream
     *
     * @var bool
     */
    protected bool $stream = false;

    /**
     * raw
     *
     * @var null | bool
     */
    protected ?bool $raw = null;

    /**
     * agent
     *
     * @var null | string
     */
    protected ?string $agent = null;

    /**
     * Base64 encoded image.
     *
     * @var null | string
     */
    protected ?string $image = null;

    /**
     * Base64 encoded images.
     *
     * @var null | array
     */
    protected ?array $images = [];

    /**
     * keep alive
     *
     * @var string
     */
    protected string $keepAlive = "5m";

    /**
     * Ollama class constructor.
     */
    public function __construct(ModelService $modelService)
    {
        $this->modelService = $modelService;
        $this->model = config('ollama-laravel.model', 'llama2');
        $this->selectedModel = $this->model;
    }

    /**
     * Sets the agent for generation.
     *
     * @param string $agent
     * @return $this
     */
    public function agent(string $agent): Ollama
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
    public function prompt(string $prompt): Ollama
    {
        $this->prompt = $prompt;

        return $this;
    }

    /**
     * Sets the model for subsequent operations.
     *
     * @param string $model
     * @return $this
     */
    public function model(string $model): Ollama
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
    public function format(string $format): Ollama
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
    public function options(array $options = []): Ollama
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
    public function stream(bool $stream = false): Ollama
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * Sets tools for generation if supported in chat.
     *
     * @param array $tools
     * @return $this
     */
    public function tools(array $tools = []): Ollama
    {
        $this->tools = $tools;

        return $this;
    }

    /**
     * Sets whether to return the response in raw format.
     *
     * @param bool $raw
     * @return $this
     */
    public function raw(bool $raw): Ollama
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Controls how long the model will stay loaded into memory following the request
     *
     * @param string $keepAlive
     * @return $this
     */
    public function keepAlive(string $keepAlive): Ollama
    {
        $this->keepAlive = $keepAlive;

        return $this;
    }

    /**
     * Lists available local models.
     *
     * @return array
     * @throws GuzzleException
     */
    public function models(): array
    {
        return $this->modelService->listLocalModels();
    }

    /**
     * Shows information about the selected model.
     *
     * @return array
     * @throws GuzzleException
     */
    public function show(): array
    {
        return $this->modelService->showModelInformation($this->selectedModel);
    }

    /**
     * Copies a model.
     *
     * @param string $destination
     * @return $this
     * @throws Exception|GuzzleException
     */
    public function copy(string $destination): Ollama
    {
        $this->modelService->copyModel($this->selectedModel, $destination);

        return $this;
    }

    /**
     * Deletes a model.
     *
     * @return $this
     * @throws Exception|GuzzleException
     */
    public function delete(): Ollama
    {
        $this->modelService->deleteModel($this->selectedModel);

        return $this;
    }

    /**
     * Pulls a model.
     *
     * @return $this
     * @throws Exception|GuzzleException
     */
    public function pull(): Ollama
    {
        $this->modelService->pullModel($this->selectedModel);

        return $this;
    }

    /**
     * Sets an image for generation.
     *
     * @param string $imagePath
     * @return $this
     * @throws Exception
     */
    public function image(string $imagePath): Ollama
    {
        if (!file_exists($imagePath)) {
            throw new Exception("Image file does not exist: $imagePath");
        }

        $this->image = base64_encode(file_get_contents($imagePath));

        return $this;
    }

    /**
     * Sets images for generation.
     *
     * @param array $imagePaths
     * @return $this
     * @throws Exception
     */
    public function images(array $imagePaths): Ollama
    {
        foreach ($imagePaths as $imagePath) {
            if (!file_exists($imagePath)) {
                throw new Exception("Image file does not exist: $imagePath");
            }

            $this->images[] = base64_encode(file_get_contents($imagePath));
        }

        return $this;
    }

    /**
     * Generates embeddings from the selected model.
     *
     * @param string $prompt
     * @return array
     * @throws Exception
     * @throws GuzzleException
     */
    public function embeddings(string $prompt): array
    {
        return $this->modelService->generateEmbeddings($this->selectedModel, $prompt);
    }

    /**
     * Generates content using the specified model.
     *
     * @return array|Response
     * @throws GuzzleException
     */
    public function ask(): array|Response
    {
        $requestData = [
            'model' => $this->model,
            'system' => $this->agent,
            'prompt' => $this->prompt,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'raw' => $this->raw,
            'keep_alive' => $this->keepAlive,
        ];

        if ($this->image) {
            $requestData['images'] = [$this->image];
        }

        if ($this->images) {
            $requestData['images'] = $this->images;
        }


        return $this->sendRequest('/api/generate', $requestData);
    }

    /**
     * Generates a chat completion using the specified model and conversation.
     *
     * @param array $conversation
     * @return array|Response
     * @throws GuzzleException
     */
    public function chat(array $conversation): array|Response
    {
        return $this->sendRequest('/api/chat', [
            'model' => $this->model,
            'messages' => $conversation,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'tools' => $this->tools,
        ]);
    }

    /**
     * @param StreamInterface $body
     * @param Closure $handleJsonObject
     * @return array
     * @throws Exception
     */
    public static function processStream(StreamInterface $body, Closure $handleJsonObject): array
    {
        return self::doProcessStream($body, $handleJsonObject);
    }
}
