<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\File;
use Contao\System;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/contao_manager/package',
    name:       Package::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Package
{
    public function __construct(
        private readonly ContaoManager $contaoManager,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack
    ){}

    #[Route('/install',
        name: 'contao_manager_package_install',
        methods: ['POST']
    )]
    public function installPackage(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $root = System::getContainer()->getParameter('kernel.project_dir');

        $filesystem = new Filesystem();
        $targetPath = $root . DIRECTORY_SEPARATOR . 'contao-manager' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        foreach ($request->toArray() as $path)
        {
            //$filesystem->copy($root . DIRECTORY_SEPARATOR  . $path, $targetPath . basename($path), true);

            // ToDo: Finding out how the transfer must take place...
            $test = (HttpClient::create())->request(
                'POST',
                $this->contaoManager->getRoute('packages/uploads'),
                [
                    'headers' => [
                        'Content-Type' => 'application/zip',
                        'Contao-Manager-Auth' => $this->contaoManager->getToken()
                    ],
                    'body' => [
                        'package' => file_get_contents($root . DIRECTORY_SEPARATOR  . $path)
                    ]
                ]
            );
        }

        /*return new JsonResponse([
            'error' => true,
            'message' => 'Fehler'
        ], 404);*/

        return new JsonResponse(['status' => 'OK']);
    }
}
