<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\Environment;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Oveleon\ProductInstaller\ContaoManagerFile;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class with helper functions for working with Contao Manager.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContaoManager
{
    /**
     * Token (Singleton).
     */
    private ?string $token = null;

    public function __construct(
        private readonly ContaoManagerFile $managerFile
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

        return $this->token = $this->managerFile->getToken();
    }

    /**
     * Returns the contao manager path.
     */
    public function getPath(): ?string
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
    public function call(string $route, string $method = 'GET', null|string $body = null, null|array $headers = null): ResponseInterface
    {
        $_headers = [
            'Content-Type' => 'application/json',
            'Contao-Manager-Auth' => $this->getToken()
        ];

        if($headers)
        {
            $_headers = array_merge($_headers, $headers);
        }

        $parameter = [
            'headers' => $_headers,
        ];

        if($body)
        {
            $parameter['body'] = $body;
        }

        // ToDo: Remove in production
        #$parameter['verify_peer'] = false;
        #$parameter['verify_host'] = false;

        return (HttpClient::create())->request(
            $method,
            $this->getRoute($route),
            $parameter
        );
    }
}
