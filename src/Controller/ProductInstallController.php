<?php

namespace Oveleon\ProductInstaller\Controller;

use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '%contao.backend.route_prefix%/installer',
    defaults: ['_scope' => 'backend', '_token_check' => false],
    methods: ['POST']
)]
class ProductInstallController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ){}

    #[Route('/check', name: 'installer_check', methods: ['POST'])]
    public function checkLicense(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest()->toArray();

        if(!$license = $request['license'])
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => 'Bitte geben Sie eine gültige Lizenz an.'
                ]
            ]);
        }

        // ToDo: Check license by server
        // Fixme: Simulate validation
        if($license === 'ABC')
        {
            return new JsonResponse([
                'products' => [
                    [
                        'name' => 'Vorlagen-Paket MEDIUM',
                        'version' => '1.0.0',
                        'image' => 'https://avatars.githubusercontent.com/u/44843847?s=200&v=4',
                        'description' => 'Um ein triviales Beispiel zu nehmen, wer von uns unterzieht sich je anstrengender körperlicher Betätigung, außer um Vorteile daraus zu ziehen?',
                        'registrable' => true,
                        'repository' => [
                            'company' => 'oveleon',
                            'repository' => 'content-package-1'
                        ]
                    ]
                ],
                'key' => 'license-connector-key-to-get-github-auth-key'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'fields' => [
                'license' => 'Es konnte kein Produkt unter der angegebenen Lizenz gefunden werden. Bitte überprüfen Sie Ihre Eingabe und beachten Sie Groß- und Kleinschreibung.'
            ]
        ]);
    }

    #[Route('/install/systemcheck', name: 'installer_install_systemcheck', methods: ['POST'])]
    public function installSystemCheck(): JsonResponse
    {
        // ToDo: Create Hook to create a check by other bundles

        if(InstalledVersions::isInstalled('contao-thememanager/core'))
        {
            return new JsonResponse([
                'status' => 'OK'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'messages' => [
                'Contao ThemeManager Core muss installiert sein.'
            ]
        ], Response::HTTP_NOT_ACCEPTABLE);
    }

    #[Route('/install', name: 'installer_install', methods: ['POST'])]
    public function install(): JsonResponse
    {
        // ToDo: Install based on key to get the github auth

        return new JsonResponse([
            'error' => true,
            'messages' => [
                'Coming soon...'
            ]
        ], Response::HTTP_NOT_ACCEPTABLE);
    }
}
