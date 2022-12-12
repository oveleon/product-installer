<?php

namespace Oveleon\ProductInstaller\Controller\API\LicenseConnector;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
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
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest()->toArray();

        if(!$license = $request['license'])
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $this->translator->trans('installer.license.errors.license_empty', [], 'installer')
                ]
            ]);
        }

        // ToDo: Check license by product-licenser (server)
        if($license === 'ABC')
        {
            // Fixme: Demo-Response
            return new JsonResponse([
                'products' => [
                    [
                        'name'          => 'Vorlagen-Paket MEDIUM',
                        'version'       => '1.0.0',
                        'image'         => 'https://avatars.githubusercontent.com/u/44843847?s=200&v=4',
                        'description'   => 'Um ein triviales Beispiel zu nehmen, wer von uns unterzieht sich je anstrengender körperlicher Betätigung, außer um Vorteile daraus zu ziehen?',
                        'registrable'   => true,
                        'repository'    => 'oveleon/content-package-1',
                        'tasks'         => [
                            [
                                'type'    => 'composer:update',
                                'require' => ['contao-thememanager/core'],
                                'update'  => ['contao-thememanager/core'],
                                'uploads' => false,
                                'pkey'    => null
                            ],
                            [
                                'type'    => 'composer:update',
                                'require' => ['contao-thememanager/ctm-tiny-slider'],
                                'update'  => ['contao-thememanager/ctm-tiny-slider'],
                                'uploads' => false,
                                'pkey'    => 'ABC'
                            ]
                        ]
                    ]
                ],
                'token'                 => 'license-connector-key-to-get-github-auth-token'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'fields' => [
                'license' => $this->translator->trans('installer.license.errors.license_not_found', [], 'installer')
            ]
        ]);
    }
}
