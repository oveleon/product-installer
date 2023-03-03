<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Step;

/**
 * The step class representing the component of an advertising step.
 * This step is used to show advertising banner created in the license connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class AdvertisingStep extends AbstractStep
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(self::STEP_ADVERTISING);
    }
}
