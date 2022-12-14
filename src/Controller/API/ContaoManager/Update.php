<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/contao_manager/update',
    name:       Update::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Update
{
    public function __construct(
        private readonly ContaoManager $contaoManager,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack
    ){}

    #[Route('/task',
        name: 'contao_manager_update_task',
        methods: ['POST']
    )]
    public function updateByTasks(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();


        return new JsonResponse([
            'error' => true,
            'message' => 'Dieses Feature steht derzeit noch nicht zur Verfügung.'
        ], 404);
        //return new JsonResponse($response, $status->getStatusCode());
    }
}
