<?php

namespace Oveleon\ProductInstaller\Download\GitHub;

use Contao\File;
use Github\AuthMethod;
use Github\Client;

/**
 * Class for downloading GitHub repositories and storing them as archives.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class RepositoryDownloader
{
    protected Client $client;

    protected string $token;
    protected string $organization;
    protected string $repository;

    /**
     * Set the authentication token.
     */
    public function setAuthentication(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the repository name.
     */
    public function setRepository(string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set the owners organization name or user.
     */
    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Download and archive repository.
     */
    public function archive($destination): void
    {
        $this->authenticate();

        $archiveContent = $this->client
                             ->api('repo')
                             ->contents()
                             ->archive($this->organization, $this->repository, 'zipball');

        $archive = new File($destination);
        $archive->write($archiveContent);
        $archive->close();
    }

    /**
     * Authenticate.
     */
    private function authenticate(): void
    {
        $this->client = new Client();
        $this->client->authenticate($this->token, null, AuthMethod::ACCESS_TOKEN);
    }
}
