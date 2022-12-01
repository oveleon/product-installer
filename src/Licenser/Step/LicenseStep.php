<?php

namespace Oveleon\ProductInstaller\Licenser\Step;

/**
 * The step class representing the component of the license step.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class LicenseStep extends AbstractStep
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(self::STEP_LICENSE);
    }
}
