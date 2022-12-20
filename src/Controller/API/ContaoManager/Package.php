<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\System;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[Route('/install',
        name: 'contao_manager_package_install',
        methods: ['POST']
    )]
    public function installPackage(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $root = System::getContainer()->getParameter('kernel.project_dir');

        $uploads = (HttpClient::create())->request(
            'GET',
            $this->contaoManager->getRoute('packages/uploads'),
            [
                'headers' => [
                    'Contao-Manager-Auth' => $this->contaoManager->getToken()
                ]
            ]
        );

        $collection = $uploads->toArray(false) ?? [];
        $uploadedPackages = array_map(fn($upload): string => $upload['name'], $collection);

        foreach ($request->toArray() as $path)
        {
            $packagePath = $root . DIRECTORY_SEPARATOR  . $path;
            $packageName = basename($path);

            // Skip already uploaded packages
            if(in_array($packageName, $uploadedPackages))
            {
                continue;
            }

            $formData = new FormDataPart([
                'package' => new DataPart(
                    file_get_contents($packagePath),
                    $packageName,
                    'application/zip'
                )
            ]);

            $header = $formData->getPreparedHeaders()->toArray();
            $header['Contao-Manager-Auth'] = $this->contaoManager->getToken();

            $response = (HttpClient::create())->request(
                'POST',
                $this->contaoManager->getRoute('packages/uploads'),
                [
                    'headers' => $header,
                    'body' => $formData->bodyToString()
                ]
            );

            if($response->getStatusCode() !== Response::HTTP_OK)
            {
                return new JsonResponse([
                    'error' => true,
                    'message' => 'Das Paket konnte nicht hinterlegt werden.'
                ], $response->getStatusCode());
            }

            $package = $response->toArray(false)['data'];
            $collection[$package['id']] = $package;
        }

        return new JsonResponse([
            'status'     => 'OK',
            'collection' => $collection
        ]);
    }
}
