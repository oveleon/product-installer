<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractPrompt
{
    public function __construct(
        protected string $name,
        protected ImportPromptType $type
    ){}

    public function getResponse(): JsonResponse
    {
        return new JsonResponse([
            'type'  => $this->type->value,
            'name'  => $this->name,
            'data'  => $this->setResponse()
        ]);
    }

    abstract protected function setResponse(): array;
}
