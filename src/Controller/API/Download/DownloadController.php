<?php

namespace Oveleon\ProductInstaller\Controller\API\Download;

use Contao\System;
use Exception;
use Oveleon\ProductInstaller\Download\FileDownloader;
use Oveleon\ProductInstaller\Download\GitHub\RepositoryDownloader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/content/download',
    name:       DownloadController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class DownloadController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly FileDownloader $fileDownloader,
        private readonly RepositoryDownloader $githubDownloader
    ){}

    /**
     * Download files:
     *
     * [
     *      [
     *          'provider': 'server',
     *          'source':   'path/to/download.zip'
     *      ],[
     *          'provider': 'github',
     *          'source':   'namespace/package',
     *          'token':    'x-y-z'
     *      ]
     * ]
     *
     * @throws Exception
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $basePath = System::getContainer()->getParameter('product_installer.installer_path') . '/downloads/';
        $response = [];

        foreach ($request->toArray() as $package)
        {
            switch ($package['provider'])
            {
                case 'github':
                    [$organization, $repository] = explode("/", $package['source']);
                    $destination = $basePath . $organization .'-'. $repository .'.zip';

                    $this->githubDownloader
                        ->setOrganization($organization)
                        ->setRepository($repository)
                        ->setAuthentication($package['token'])
                        ->archive($destination);

                    $package['destination'] = $destination;
                    $response[] = $package;

                    break;

                case 'server':
                    $destination = $basePath . basename($package['repository']);

                    $this->fileDownloader
                        ->download($package['repository'], $destination);

                    $package['destination'] = $destination;
                    $response[] = $package;

                    break;

                default:
                    return new JsonResponse([
                        'error'   => true,
                        'message' => 'The specified provider is not supported.'
                    ], Response::HTTP_NOT_ACCEPTABLE);
            }
        }

        return new JsonResponse($response);
    }
}
