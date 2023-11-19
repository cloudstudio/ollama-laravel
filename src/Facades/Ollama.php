<?php

namespace Cloudstudio\Ollama\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cloudstudio\Ollama\Ollama
 */
class Ollama extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Cloudstudio\Ollama\Ollama::class;
    }
}
