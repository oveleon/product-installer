<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\Environment;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
        // ToDo: Use .env.installer instead of database

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

    /**
     * Returns the current session status
     *
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function getStatus(): ResponseInterface
    {
        return (HttpClient::create())->request(
            'GET',
            $this->getRoute('session'),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Contao-Manager-Auth' => $this->getToken()
                ]
            ]
        );
    }
}
