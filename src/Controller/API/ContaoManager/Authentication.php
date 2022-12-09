<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('%contao.backend.route_prefix%/api/contao_manager/auth',
    name:       Authentication::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['GET']
)]
class Authentication
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack
    ){}

    /**
     * @throws Exception
     */
    public function __invoke(): JsonResponse
    {
        $request   = $this->requestStack->getCurrentRequest();
        $container = System::getContainer();

        $config = $this->connection->fetchAllAssociative("SELECT id, contao_manager_token FROM tl_product_installer");

        if (empty($config))
        {
            // Check whether the authorization has really been approved
            if($request->get('token'))
            {
                $this->connection->insert(
                    "tl_product_installer",
                    [
                        'contao_manager_token' => $request->get('token')
                    ]
                );
            }
        }
        else
        {
            $this->connection->update("tl_product_installer", [
                'contao_manager_token' => $request->get('token')
            ], [
                'id' => $config[0]['id']
            ]);
        }

        $parameter = http_build_query([
            'installer' => $request->get('installer'),
            'start'     => $request->get('start')
        ]);

        throw new RedirectResponseException($request->getSchemeAndHttpHost() . $container->getParameter('contao.backend.route_prefix') . '?' . $parameter);
    }
}
