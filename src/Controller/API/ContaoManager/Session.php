<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/contao_manager_authorization',
    name:       Session::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Session
{
    /**
     * Authentications types
     */
    const AUTH_USER = 'user';
    const AUTH_TOKEN = 'token';

    /**
     * Authentication status codes
     */
    const STATUS_AUTHENTICATED = 200;
    const STATUS_NOT_AUTHENTICATED = 401;
    const STATUS_LOCKED = 403;
    const STATUS_NO_RECORDS = 204;

    public function __construct(
        private readonly RequestStack $requestStack
    ){}

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();

        $status = $this->getStatus();

        // Is authenticated // ToDo: use match to handle all states
        if($status->getStatusCode() === self::STATUS_AUTHENTICATED)
        {
            return new JsonResponse([
                'status' => 'OK'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'message' => 'Not authorized'
        ]);
    }

    private function getStatus(): ResponseInterface
    {
        return (HttpClient::create())->request(
            'GET',
            'http://contao413.local/contao-manager.phar.php/api/session',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Contao-Manager-Auth' => '4a6417378a89ae4d7848a8a691a40839'
                ]
            ]
        );
    }
}
