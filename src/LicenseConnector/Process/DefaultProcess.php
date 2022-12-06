<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Process;

/**
 * The process class representing the component of the system check process.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 *
 * Attributes:
 * @property $title
 * @property $description
 */
class DefaultProcess extends AbstractProcess
{
    /**
     * Defines the standardized name of the route for retrieving and checking the process.
     *
     * @required
     */
    const ROUTE_PROCESS = 'process';

    /**
     * @inheritDoc
     */
    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct(self::PROCESS_DEFAULT);
    }
}
