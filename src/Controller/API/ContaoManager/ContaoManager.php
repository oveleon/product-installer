<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\Environment;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ContaoManager
{
    /**
     * Token (Singleton).
     */
    private ?string $token = null;

    public function __construct(
        private readonly Connection $connection
    ){}

    /**
     * Returns the contao manager token.
     *
     * @throws Exception
     */
    public function getToken(): ?string
    {
        if($this->token)
        {
            return $this->token;
        }

        return $this->token = ($this->connection->fetchOne("SELECT contao_manager_token FROM tl_product_installer") ?: null);
    }

    /**
     * Returns the contao manager path.
     */
    public function getPath(): string
    {
        return System::getContainer()->getParameter('contao_manager.manager_path');
    }

    /**
     * Returns the absolute path to communicate with the contao manager api.
     */
    public function getRoute(string $apiRoute): string
    {
        return Environment::get('url') . '/' . $this->getPath() . '/api/' . $apiRoute;
    }
}
