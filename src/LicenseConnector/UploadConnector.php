<?php

namespace Oveleon\ProductInstaller\LicenseConnector;

use Contao\Controller;
use Symfony\Component\HttpFoundation\Request;
use Oveleon\ProductInstaller\LicenseConnector\Process\ContaoManagerProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\RegisterProductProcess;
use Oveleon\ProductInstaller\LicenseConnector\Step\ContaoManagerStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\LicenseStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\ProcessStep;

/**
 * Configuration class for the licensor of upload able products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class UploadConnector extends AbstractLicenseConnector
{
    function setSteps(): void
    {
        $router = Controller::getContainer()->get('router');
        $translator = Controller::getContainer()->get('translator');

        // Create steps
        $this->addSteps(
            // Add license step
            new LicenseStep(),

            // Add contao manager step
            new ContaoManagerStep(),

            // Add install process step
            (new ProcessStep())
                ->addProcesses(
                    new ContaoManagerProcess(),
                    new RegisterProductProcess()
                )
        );
    }

    /**
     * @inheritDoc
     */
    function getConfig(): array
    {
        $translator = Controller::getContainer()->get('translator');
        $request = Request::createFromGlobals();

        return [
            'name'          => 'Upload',
            'title'         => $translator->trans('installer.connector.upload.title', [], 'installer'),
            'description'   => $translator->trans('installer.connector.upload.description', [], 'installer'),
            'image'         => '/bundles/productinstaller/icons/logo.svg',
            'entry'         => $request->getSchemeAndHttpHost() . '/api/upload'
        ];
    }
}
