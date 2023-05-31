<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

class ConfirmPrompt extends AbstractPrompt
{
    private string $question;
    private array $answers = [];

    public function __construct(string $name)
    {
        parent::__construct($name, ImportPromptType::CONFIRM);
    }

    public function question(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function answer(string $label, string $value): self
    {
        $this->answers[] = [$label, $value];

        return $this;
    }

    protected function setResponse(): array
    {
        return [
            'question' => $this->question,
            'answers'  => $this->answers,
        ];
    }
}
