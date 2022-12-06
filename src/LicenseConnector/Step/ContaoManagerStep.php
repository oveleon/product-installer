<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Step;

/**
 * The step class representing the component of the component manager step.
 * This step is used to check if a connection to the Contao Manager can be established.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContaoManagerStep extends AbstractStep
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(self::STEP_CONTAO_MANAGER);
    }
}
