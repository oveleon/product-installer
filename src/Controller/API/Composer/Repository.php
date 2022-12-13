<?php

namespace Oveleon\ProductInstaller\Controller\API\Composer;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\File;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/composer/repositories',
    name:       Repository::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['GET']
)]
class Repository
{
    public function __construct(
        private readonly Composer $composer,
        private readonly RequestStack $requestStack
    ){}

    #[Route('/set',
        name: 'composer_repositories_set',
        methods: ['POST']
    )]
    public function set(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $hasChanges = false;

        foreach($request->toArray() as $task)
        {
            foreach ($task['composer']['repositories'] ?? [] as $repository)
            {
                if(!$this->composer->hasRepository($repository['url']))
                {
                    $this->composer->addRepository($repository['type'], $repository['url']);
                    $hasChanges = true;
                }
            }
        }

        if($hasChanges)
        {
            $this->composer->save();
        }

        return new JsonResponse(['status' => 'OK']);
    }
}
