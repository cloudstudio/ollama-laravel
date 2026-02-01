<?php

namespace Cloudstudio\Ollama;

use Cloudstudio\Ollama\Services\ModelService;
use Cloudstudio\Ollama\Traits\MakesHttpRequests;
use Cloudstudio\Ollama\Traits\StreamHelper;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\StreamInterface;

/**
 * Ollama class for integration with Laravel.
 */
class Ollama
{
    use MakesHttpRequests;
    use StreamHelper;

    /**
     * modelService
     *
     * @var mixed
     */
    protected $modelService;

    /**
     * selectedModel
     *
     * @var mixed
     */
    protected $selectedModel;

    /**
     * model
     *
     * @var mixed
     */
    protected $model;

    /**
     * prompt
     *
     * @var mixed
     */
    protected $prompt;

    /**
     * format
     *
     * @var mixed
     */
    protected $format;

    /**
     * options
     *
     * @var mixed
     */
    protected $options;

    /**
     * options
     *
     * @var mixed
     */
    protected $tools;

    /**
     * stream
     *
     * @var bool
     */
    protected $stream = false;

    /**
     * raw
     *
     * @var mixed
     */
    protected $raw;

    /**
     * agent
     *
     * @var mixed
     */
    protected $agent;

    /**
     * Base64 encoded image.
     *
     * @var string|null
     */
    protected $image = null;

    /**
     * Base64 encoded images.
     *
     * @var array|null
     */
    protected $images = [];

    /**
     * Controls how long the model stays loaded in memory.
     * Set to null to use server default, or a duration string like "5m", "1h", etc.
     *
     * @var string|null
     */
    protected $keepAlive = null;

    /**
     * Enables thinking/reasoning output for supported models.
     * Can be a boolean or a string level ("low", "medium", "high") for models like GPT-OSS.
     *
     * @var bool|string|null
     */
    protected $think = null;

    /**
     * Ollama class constructor.
     */
    public function __construct(ModelService $modelService)
    {
        $this->modelService = $modelService;
        $this->model = config('ollama-laravel.model');
        $this->keepAlive = config('ollama-laravel.keep_alive', null);
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
        $this->prompt = $prompt;
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
     * @param string|array $format
     * @return $this
     */
    public function format($format)
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
    public function options(array $options = [])
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
    public function stream(bool $stream = false)
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
    public function tools(array $tools = [])
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
    public function raw(bool $raw)
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * Controls how long the model will stay loaded into memory following the request.
     *
     * Examples:
     * - keepAlive('5m') - Keep model loaded for 5 minutes
     * - keepAlive('1h') - Keep model loaded for 1 hour
     * - keepAlive(null) - Use server default configuration (respects OLLAMA_KEEP_ALIVE env var)
     *
     * @param string|null $keepAlive Duration string (e.g., "5m", "1h") or null to use server default
     * @return $this
     */
    public function keepAlive(?string $keepAlive)
    {
        $this->keepAlive = $keepAlive;
        return $this;
    }

    /**
     * Enables thinking/reasoning output for supported models (Qwen 3, DeepSeek R1, etc.).
     *
     * When enabled, the response will include the model's reasoning process in a separate field:
     * - For chat: response['message']['thinking']
     * - For generate: response['thinking']
     *
     * Examples:
     * - think() or think(true) - Enable thinking output
     * - think(false) - Disable thinking output
     * - think('high') - Set thinking level (for models like GPT-OSS that support levels)
     *
     * @param bool|string $think Boolean to enable/disable, or string level ("low", "medium", "high")
     * @return $this
     */
    public function think(bool|string $think = true)
    {
        $this->think = $think;
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
     * Sets an image for generation.
     *
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    public function image(string $imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file does not exist: $imagePath");
        }

        $this->image = base64_encode(file_get_contents($imagePath));
        return $this;
    }

    /**
     * Sets images for generation.
     *
     * @param array $imagePaths
     * @return $this
     * @throws \Exception
     */
    public function images(array $imagePaths)
    {
        foreach ($imagePaths as $imagePath) {
            if (!file_exists($imagePath)) {
                throw new \Exception("Image file does not exist: $imagePath");
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
     * @throws \Exception
     */
    public function embeddings(string $prompt)
    {
        return $this->modelService->generateEmbeddings($this->selectedModel, $prompt);
    }

    /**
     * Generates content using the specified model.
     *
     * @return array|Response
     */
    public function ask()
    {
        $requestData = [
            'model' => $this->model,
            'system' => $this->agent,
            'prompt' => $this->prompt,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'raw' => $this->raw,
        ];

        if ($this->keepAlive !== null) {
            $requestData['keep_alive'] = $this->keepAlive;
        }

        if ($this->think !== null) {
            $requestData['think'] = $this->think;
        }

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
     * @return array
     */
    public function chat(array $conversation)
    {
        $requestData = [
            'model' => $this->model,
            'messages' => $conversation,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'tools' => $this->tools,
        ];

        if ($this->keepAlive !== null) {
            $requestData['keep_alive'] = $this->keepAlive;
        }

        if ($this->think !== null) {
            $requestData['think'] = $this->think;
        }

        return $this->sendRequest('/api/chat', $requestData);
    }


    /**
     * @param StreamInterface $body
     * @param \Closure $handleJsonObject
     * @return array
     * @throws \Exception
     */
    public static function processStream(StreamInterface $body, \Closure $handleJsonObject): array {
        return self::doProcessStream($body, $handleJsonObject);
    }
}
