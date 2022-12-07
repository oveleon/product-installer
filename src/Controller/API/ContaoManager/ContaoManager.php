<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ContaoManager
{
    public function __construct(
        private readonly Connection $connection
    ){}

    /**
     * Returns the contao manager token
     *
     * @throws Exception
     */
    public function getToken(): ?string
    {
        return $this->connection->fetchOne("SELECT contao_manager_token FROM tl_product_installer") ?: null;
    }
}
