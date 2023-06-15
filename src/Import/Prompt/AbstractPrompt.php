<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Abstract class to create a prompt.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class AbstractPrompt
{
    protected array $customResponseData = [];

    public function __construct(
        protected string $name,
        protected ImportPromptType $type
    ){}

    public function setCustomResponseData(array $responseData): void
    {
        $this->customResponseData = $responseData;
    }

    public function getResponse(): JsonResponse
    {
        return new JsonResponse([
            ...[
                'type'  => $this->type->value,
                'name'  => $this->name,
                'data'  => $this->setResponse()
            ],
            ...$this->customResponseData
        ]);
    }

    abstract protected function setResponse(): array;
}
