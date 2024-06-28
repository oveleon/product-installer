<?php

namespace Oveleon\ProductInstaller\Controller\API\Composer;

use Contao\BackendUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class to write the "repositories" branch to the composer file.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/composer/repositories',
    name:       Repository::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['GET']
)]
class Repository
{
    public function __construct(
        private readonly Composer $composer,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ){}

    #[Route('/set',
        name: 'composer_repositories_set',
        methods: ['POST']
    )]
    public function set(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin)
        {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

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
