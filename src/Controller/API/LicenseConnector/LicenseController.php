<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Oveleon\ProductInstaller\Util\ConnectorUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/api/license_connector/license',
    name:       LicenseController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class LicenseController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ConnectorUtil $connectorUtil
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        // Check if a license has been submitted
        if(!$license = $parameter['license'])
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $this->translator->trans('installer.license.errors.license_empty', [], 'installer')
                ]
            ]);
        }

        // Get current connector
        if(!$connector = $this->connectorUtil->getConnectorByName($parameter['connector']))
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => 'No license connector found.'
                ]
            ]);
        }

        // Check license via connector
        $response = $this->connectorUtil->post(
            $connector['connector'],
            '/license/check',
            array_merge(
                $parameter,
                [
                    'locale' => $request->getLocale(),
                    'host'   => $request->getHost()
                ]
            )
        );

        // Check whether a connection could be established
        if($response->getStatusCode() !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => 'No connection can be established at the moment, please try again later.'
                ]
            ]);
        }

        $licenseInformation = $response->toArray();

        if($error = ($licenseInformation['error'] ?? false))
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $error
                ]
            ]);
        }

        if($license === 'TEST')
        {
            return new JsonResponse([
                'products' => [
                    [
                        'name'          => 'Content Paket',
                        'version'       => '1.0.0',
                        'image'         => 'https://avatars.githubusercontent.com/u/44843847?s=200&v=4',
                        'description'   => 'Um ein triviales Beispiel zu nehmen, wer von uns unterzieht sich je anstrengender körperlicher Betätigung, außer um Vorteile daraus zu ziehen?',
                        'registrable'   => true,
                        'tasks'         => [
                            [
                                'type'    => 'repo_import',
                                'provider' => 'github',
                                'repository' => 'oveleon/content-package-1',
                            ],
                            [
                                'type'       => 'manager_package',
                                'provider'   => 'server',
                                'source'     => 'https://p607045.mittwaldserver.info/share/product-exporter.zip'
                            ],
                            /*[
                                'type'       => 'manager:package',
                                'provider'   => 'github',
                                'source'     => 'oveleon/product-exporter',
                                'token'      => 'XYZ'
                            ],*/
                            /*[
                                'type'     => 'composer:update',
                                'provider' => 'gitlab',
                                'require'  => ['oveleon/product-exporter' => '0.1'],
                                'update'   => ['oveleon/product-exporter'],
                                'composer' => [
                                    'repositories' => [
                                        [
                                            'type' => 'vcs',
                                            'url'  => 'https://gitlab+deploy-token-1585707:EzG_8siAuE8Rf9sGh4Rj@gitlab.com/oveleon/product-exporter.git'
                                        ]
                                    ]
                                ]
                            ]*/
                            /*[
                                'type'     => 'composer_update',
                                'require'  => ['oveleon/contao-cookiebar' => '^1.12'],
                                'update'   => ['oveleon/contao-cookiebar']
                            ]*/
                        ]
                    ],[
                        'name'          => 'Skin',
                        'version'       => '1.0.0',
                        'image'         => 'https://avatars.githubusercontent.com/u/44843847?s=200&v=4',
                        'description'   => 'Um ein triviales Beispiel zu nehmen, wer von uns unterzieht sich je anstrengender körperlicher Betätigung, außer um Vorteile daraus zu ziehen?',
                        'registrable'   => true,
                        'tasks'         => [
                            [
                                'type'     => 'composer_update',
                                'require'  => ['oveleon/contao-cookiebar' => '5.x-dev'],
                                'update'   => ['oveleon/contao-cookiebar']
                            ]
                        ]
                    ]
                ],
                'token' => 'license-connector-key-to-get-github-auth-token'
            ]);
        }

        return new JsonResponse([
            'products' => $licenseInformation['products'] ?? []
        ]);
    }
}
