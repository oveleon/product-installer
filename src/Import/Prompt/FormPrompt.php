<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

use Oveleon\ProductInstaller\Import\ImportPromptType;

class FormPrompt extends AbstractPrompt
{
    public function __construct(string $name)
    {
        parent::__construct($name, ImportPromptType::FORM);
    }

    protected function setResponse(): array
    {
        return [
            'fields'  => [
                'feld1' => 'Hallo!'
            ]
        ];
    }
}
