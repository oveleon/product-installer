<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

class FormPrompt extends AbstractPrompt
{
    protected array $fields = [];
    protected array $options = [];

    public function __construct(string $name)
    {
        parent::__construct($name, ImportPromptType::FORM);
    }

    public function field(string $name, array|string $values, FormPromptType|string $type, array $options = []): self
    {
        $this->fields[] = [
            'name'    => $name,
            'value'   => $values,
            'type'    => $type,
            'options' => $options
        ];

        return $this;
    }

    protected function setResponse(): array
    {
        return [
            'fields'  => $this->fields,
            'options' => $this->options
        ];
    }
}
