<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;

abstract class AbstractPromptImport
{
    /**
     * Contains the PromptResponse.
     */
    protected PromptResponse $promptResponse;

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
}
