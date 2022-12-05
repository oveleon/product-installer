<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Step;

/**
 * The step class representing the component of the license step.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class LicenseStep extends AbstractStep
{
    /**
     * Defines the standardized name of the route for retrieving and checking the licenses.
     *
     * @required
     */
    const ROUTE_CHECK_LICENSE = 'license';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(self::STEP_LICENSE);
    }
}