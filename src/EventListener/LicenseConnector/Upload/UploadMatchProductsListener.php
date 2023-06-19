<?php

namespace Oveleon\ProductInstaller\EventListener\LicenseConnector\Upload;

use Contao\System;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class for verification of products of the Upload License Connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class UploadMatchProductsListener
{
    public function matchProducts(string $connectorName, array $remoteProducts, array $clientProducts, array &$collection): bool
    {
        if($connectorName !== 'Upload')
        {
            return false;
        }

        $filesystem = new Filesystem();
        $filepath = System::getContainer()->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR;

        foreach ($clientProducts as $clientProduct)
        {
            $remove = false;

            // Check if tasks contain a file that must exist
            foreach ($clientProduct['tasks'] ?? [] as $task)
            {
                if(($task['destination'] ?? false) && !$filesystem->exists($filepath . $task['destination']))
                {
                    $remove = true;
                    break;
                }
            }

            $collection[] = array_merge($clientProduct, [
                'registered' => true,
                'remove'     => $remove
            ]);
        }

        return true;
    }
}
