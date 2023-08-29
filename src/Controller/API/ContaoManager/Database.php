<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class for handle database tasks.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
#[Route('%contao.backend.route_prefix%/api/contao_manager/database',
    name:       Database::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class Database
{
    public function __construct(
        private readonly ContaoManager $contaoManager,
        private readonly RequestStack $requestStack
    ){}

    #[Route('/check',
        name: 'contao_manager_check_database',
        methods: ['POST']
    )]
    public function check(): JsonResponse
    {
        try {
            $response = $this->contaoManager->call(
                'server/database'
            );

            $status = $response->getStatusCode();
        }
        catch (Exception | TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status === Response::HTTP_NOT_IMPLEMENTED)
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'Contao console does not support the necessary contao:migrate commands or CLI API features.'
            ], $status);
        }

        if($status === Response::HTTP_SERVICE_UNAVAILABLE)
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'The PHP command line binary cannot be found.'
            ], $status);
        }

        if($status !== Response::HTTP_OK)
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'Oops, hier ist etwas schief gelaufen.'
            ], $status);
        }

        $output = $response->toArray();

        $output['token'] = $this->contaoManager->getToken();
        $output['updateRoute'] = $this->contaoManager->getRoute('system/database');

        return new JsonResponse($output, $status);
    }

    #[Route('/migrate-status',
        name: 'contao_manager_migrate_status',
        methods: ['POST']
    )]
    public function migrateStatus(): JsonResponse
    {
        try {
            $response = $this->contaoManager->call(
                'contao/database-migration'
            );

            $status = $response->getStatusCode();
        }
        catch (Exception | TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status === Response::HTTP_NOT_IMPLEMENTED)
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'Your Contao version does not support the database migration API.'
            ], $status);
        }

        $output = ['create' => true];

        if($status !== Response::HTTP_NO_CONTENT)
        {
            $output = $response->toArray();
        }

        $output['originalStatus'] = $status;

        return new JsonResponse($output, Response::HTTP_OK);
    }

    #[Route('/create-migrate',
        name: 'contao_manager_migrate_database',
        methods: ['POST']
    )]
    public function createMigrate(): JsonResponse
    {
        try {
            $response = $this->contaoManager->call(
                'contao/database-migration',
                'PUT',
                json_encode([
                    'skipWarnings' => false,
                    'type' => ''
                ])
            );

            $status = $response->getStatusCode();
        }
        catch (Exception | TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status === Response::HTTP_NOT_IMPLEMENTED)
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'Your Contao version does not support the database migration API.'
            ], $status);
        }

        if($status === Response::HTTP_CREATED)
        {
            // Get status after creating
            $response = $this->contaoManager->call(
                'contao/database-migration'
            );

            $output = $response->toArray();
        }

        $output['originalStatus'] = $status;

        return new JsonResponse($output, Response::HTTP_OK);
    }

    #[Route('/start-migrate',
        name: 'contao_manager_migrate_start',
        methods: ['POST']
    )]
    public function startMigrate(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $parameter = $request->toArray();

        try {
            $response = $this->contaoManager->call(
                'contao/database-migration',
                'PUT',
                json_encode([
                    'hash' => $parameter['hash'],
                    'type' => $parameter['type'] ?? '',
                    'withDeletes' => $parameter['delete'] ?? false
                ])
            );

            $status = $response->getStatusCode();
        }
        catch (Exception | TransportExceptionInterface $e)
        {
            return new JsonResponse([
                'error'  => true,
                'message' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if($status === Response::HTTP_NOT_IMPLEMENTED)
        {
            return new JsonResponse([
                'error' => true,
                'message' => 'The current Contao version does not support the database migration API.'
            ], $status);
        }

        // Is a migration is running, delete it and start again
        if($status === Response::HTTP_BAD_REQUEST)
        {
            // Get status after creating
            $this->deleteMigrate();

            return $this->startMigrate();
        }

        $response = $this->contaoManager->call(
            'contao/database-migration'
        );

        $output = $response->toArray();

        return new JsonResponse($output, $status);
    }

    #[Route('/delete-migrate',
        name: 'contao_manager_migrate_delete',
        methods: ['DELETE']
    )]
    public function deleteMigrate(): JsonResponse
    {
        // Get status after creating
        $this->contaoManager->call(
            'contao/database-migration',
            'DELETE'
        );

        return new JsonResponse(['OK']);
    }
}
