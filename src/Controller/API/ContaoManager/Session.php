<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/contao_manager/session',
    name:       Session::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Session
{
    /**
     * Authentication status codes
     */
    const STATUS_AUTHENTICATED = 200;
    const STATUS_NOT_AUTHENTICATED = 401;
    const STATUS_LOCKED = 403;
    const STATUS_NO_RECORDS = 204;

    public function __construct(
        private readonly ContaoManager $contaoManager,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack
    ){}

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();

        // Check if the manager is installed
        if(!$managerPath = $this->contaoManager->getPath())
        {
            return new JsonResponse([
                'error' => true,
                'exists' => false,
                'message' => 'Contao Manager is not installed.'
            ]);
        }

        // Create default response
        $response = [
            'manager' => [
                'path'       => $managerPath,
                'return_url' => $request->getSchemeAndHttpHost() . $this->router->generate(Authentication::class)
            ]
        ];

        if(null === $this->contaoManager->getToken())
        {
            return new JsonResponse([...$response, ...[
                'error' => true,
                'exists' => true,
                'message' => 'Not authorized.'
            ]]);
        }

        // If we have access to the contao manager, get the status to check if the token is still active
        $status = $this->contaoManager->call('session');

        switch ($status->getStatusCode())
        {
            case self::STATUS_AUTHENTICATED:
                $response['status'] = 'OK';
                break;

            case self::STATUS_LOCKED:
                $response['error'] = true;
                $response['message'] = 'The Contao Manager is locked.';
                break;

            case self::STATUS_NOT_AUTHENTICATED:
                $response['error'] = true;
                $response['message'] = 'Not authorized.';
                break;

            case self::STATUS_NO_RECORDS:
                $response['error'] = true;
                $response['message'] = 'No user records found.';
                break;
        }

        return new JsonResponse($response, $status->getStatusCode());
    }
}
