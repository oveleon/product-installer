<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;
use Oveleon\ProductInstaller\SetupLock;

abstract class AbstractPromptImport
{
    /**
     * Defines the current prompt.
     */
    protected ?AbstractPrompt $prompt = null;

    /**
     * Defines conditions.
     */
    protected ?array $conditions = null;

    /**
     * Contains the PromptResponse.
     */
    protected PromptResponse $promptResponse;

    public function __construct(
        protected readonly SetupLock $setupLock
    ){}

    /**
     * Returns a PromptResponse by name.
     */
    public function getPromptResponse(): PromptResponse
    {
        return $this->promptResponse;
    }

    /**
     * Sets a PromptResponse.
     */
    public function setPromptResponse(PromptResponse $promptResponse): void
    {
        $this->promptResponse = $promptResponse;
    }

    /**
     * Sets the setup scope.
     */
    public function setScope(string $scope)
    {
        $this->setupLock->setScope($scope);
    }

    /**
     * Sets the prompt to be returned.
     */
    public function setPrompt(AbstractPrompt $prompt): void
    {
        $this->prompt = $prompt;
    }

    /**
     * Set import conditions.
     */
    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * Return conditions by key.
     */
    protected function getConditions(string $key): ?array
    {
        if(\array_key_exists($key, $this->conditions))
        {
            return $this->conditions[$key];
        }

        return null;
    }
}
