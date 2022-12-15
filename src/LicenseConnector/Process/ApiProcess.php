<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Process;

/**
 * The process class representing the component of the api process.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 *
 * Attributes:
 * @property $title
 * @property $description
 */
class ApiProcess extends AbstractProcess
{
    /**
     * Defines the standardized name of the route for retrieving and checking the process.
     *
     * @required
     */
    const ROUTE = 'api';

    /**
     * @inheritDoc
     */
    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct(self::PROCESS_API);
    }
}
