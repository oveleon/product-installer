<?php

namespace Oveleon\ProductInstaller\Util;

use Contao\Controller;
use Oveleon\ProductInstaller\LicenseConnector\AbstractLicenseConnector;
use Oveleon\ProductInstaller\LicenseConnector\Step\AbstractStep;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpClient\HttpClient;

class ConnectorUtil
{
    public function post(AbstractLicenseConnector $connector, string $route, array $body): ResponseInterface
    {
        return (HttpClient::create())->request(
            'POST',
            $connector->getConfig()['entry'] . $route,
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Cache-Control' => 'no-cache'
                ],
                'json' => $body
            ]
        );
    }

    public function getConnectorByName(string $nane, bool $includeSteps = false): ?array
    {
        if($connectors = $this->getConnectors($includeSteps))
        {
            foreach ($connectors as $connector)
            {
                if($connector['config']['name'] === $nane)
                {
                    return $connector;
                }
            }
        }

        return null;
    }

    public function getConnectors(bool $includeSteps = true): ?array
    {
        $licenseConnectors = Controller::getContainer()->getParameter('product_installer.license_connectors');

        if(!empty($licenseConnectors))
        {
            $collection = [];

            foreach ($licenseConnectors as $licenseConnector)
            {
                /** @var AbstractLicenseConnector $licenseConnector */
                $licenseConnector = new $licenseConnector();
                $steps = [];

                if($includeSteps)
                {
                    /** @var AbstractStep $step */
                    foreach ($licenseConnector->getSteps() as $step)
                    {
                        $stepConfig = [
                            'name'       => $step->name,
                            'routes'     => $step->getRoutes(),
                            'attributes' => $step->getAttributes()
                        ];

                        $steps[] = $stepConfig;
                    }
                }

                $collection[] = [
                    'connector' => $licenseConnector,
                    'config'    => $licenseConnector->getConfig(),
                    'steps'     => $steps
                ];
            }

            return $collection;
        }

        return null;
    }
}
