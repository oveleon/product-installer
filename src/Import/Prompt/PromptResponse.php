<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

/**
 * Class to simplify working with arrays created by a prompt.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class PromptResponse
{
    public function __construct(private ?array $response)
    {}

    public function has(string $key): bool
    {
        if(!$this->response)
        {
            return false;
        }

        return !!($this->response[$key] ?? false);
    }

    public function get(string $key, null|string|array $default = null): null|string|array
    {
        if(!$this->response)
        {
            return $default;
        }

        return $this->response[$key] ?? $default;
    }
}
