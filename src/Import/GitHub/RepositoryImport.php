<?php

namespace Oveleon\ProductInstaller\Import\GitHub;

use Contao\File;
use Github\AuthMethod;
use Github\Client;

/**
 * Class for retrieving and importing GitHub repositories.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class RepositoryImport // ToDo: This method should called e.g. `RepositoryDownloader`; It doesnt import, only download and save
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
     * Import packages.
     */
    public function import(): void
    {
        // ToDo: This method should called e.g. `save` or `archive` ord `download`
        $this->authenticate();

        $archiveContent = $this->client
                             ->api('repo')
                             ->contents()
                             ->archive($this->organization, $this->repository, 'zipball');

        $archive = new File('system/tmp/'.$this->organization.'-'.$this->repository.'.zip');
        $archive->write($archiveContent);
        $archive->close();
    }

    private function authenticate(): void
    {
        $this->client = new Client();
        $this->client->authenticate($this->token, null, AuthMethod::ACCESS_TOKEN);
    }
}