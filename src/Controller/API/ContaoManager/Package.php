<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\System;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class for installing manager artifacts via the Contao Manager.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/contao_manager/package',
    name:       Package::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Package
{
    public function __construct(
        private readonly ContaoManager $contaoManager,
        private readonly TranslatorInterface $translator,
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

        // Get current uploads
        $uploads = $this->contaoManager->call('packages/uploads');

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

            $response = $this->contaoManager->call(
                'packages/uploads',
                'POST',
                $formData->bodyToString(),
                $formData->getPreparedHeaders()->toArray()
            );

            if($response->getStatusCode() !== Response::HTTP_OK)
            {
                return new JsonResponse([
                    'error' => true,
                    'message' => $this->translator->trans('installer.connector.errors.package_setup_fail', [], 'installer')
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
