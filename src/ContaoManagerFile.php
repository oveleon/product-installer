<?php

namespace Oveleon\ProductInstaller;

/**
 * Class to edit the local JSON file manager.json.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContaoManagerFile extends InstallerFile
{
    public function __construct()
    {
        parent::__construct('manager.json');
    }

    /**
     * Set the manager access token.
     */
    public function setToken(string $token): void
    {
        if(!$this->content)
        {
            $this->content = [
                'token' => $token
            ];

            return;
        }

        $this->content['token'] = $token;
    }

    /**
     * Returns the manager access token.
     */
    public function getToken(): ?string
    {
        if(!$this->content || !array_key_exists('token', $this->content))
        {
            return null;
        }

        return $this->content['token'];
    }
}
