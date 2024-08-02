<?php

namespace Oveleon\ProductInstaller\Controller\API\Download;

use Contao\BackendUser;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\System;
use Exception;
use Oveleon\ProductInstaller\Download\FileDownloader;
use Oveleon\ProductInstaller\Download\GitHub\RepositoryDownloader;
use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class to download data from various sources.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
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
        private readonly RepositoryDownloader $githubDownloader,
        private readonly TranslatorInterface $translator,
        private readonly ConnectorUtil $connectorUtil,
        protected TokenStorageInterface $tokenStorage,
    ){}

    /**
     * Download files:
     *
     * [
     *      [
     *          'provider': 'server',
     *          'source':   'path/to/download.zip'
     *      ],
     *      [
     *          'provider': 'shop',
     *          'product':  '18nd238923r83hrrn23r239720eh2e' // (Product hash)
     *      ],
     *      [
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
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $requestStack = $this->requestStack->getCurrentRequest();
        $request = (object) $requestStack->toArray();

        $basePath = System::getContainer()->getParameter('product_installer.installer_path') . '/downloads/';
        $response = [];

        foreach ($request->tasks as $package)
        {
            switch ($package['provider'])
            {
                // Download files from GitHub
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

                // Download files from shop server with authentication
                case 'shop':

                    // Get current connector
                    if(!$connector = $this->connectorUtil->getConnectorByName($request->connector))
                    {
                        return new JsonResponse([
                            'error'  => true,
                            'message'=> $this->translator->trans('installer.connector.errors.connection_failed_global', [], 'installer')
                        ]);
                    }

                    // Verify request
                    $shopResponse = $this->connectorUtil->post(
                        $connector['connector'],
                        '/package/verify',
                        [
                            'hash'    => $package['source'],
                            'license' => $request->license,
                            'locale'  => $requestStack->getLocale(),
                            'host'    => $requestStack->getHost(),
                            'contao_version' => ContaoCoreBundle::getVersion()
                        ]
                    );

                    try{
                        $parameter = ((object) $shopResponse->toArray());
                        $secret = $parameter->secret;
                        $destination = $basePath . $parameter->fileName;
                    }catch (\Exception $e) {
                        return new JsonResponse([
                            'error'   => true,
                            'message' => $this->translator->trans('installer.connector.errors.product_auth_failed', [], 'installer')
                        ], Response::HTTP_NOT_ACCEPTABLE);
                    }

                    $package['destination'] = $destination;

                    $this->fileDownloader
                         ->method('POST')
                         ->header('ProductInstaller: '.$secret)
                         ->parameter([
                             'contao_version' => ContaoCoreBundle::getVersion()
                         ])
                         ->download($connector['config']['entry'].'/package/download', $destination);

                    $response[] = $package;

                    break;

                // Download files from a extern server (e.g https://website.com/download/file.zip)
                case 'server':
                    $destination = $basePath . basename($package['source']);

                    $this->fileDownloader
                         ->download($package['source'], $destination);

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
