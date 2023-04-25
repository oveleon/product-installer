<?php

namespace Oveleon\ProductInstaller\EventListener\LicenseConnector\Upload;

use Contao\System;
use Symfony\Component\Filesystem\Filesystem;

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
            $collection[] = array_merge($clientProduct, [
                'registered' => true,
                'remove'     => !$filesystem->exists($filepath . $clientProduct['destination'])
            ]);
        }

        return true;
    }
}
