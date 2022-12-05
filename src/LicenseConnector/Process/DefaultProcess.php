<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Process;

/**
 * The process class representing the component of the system check process.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
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
     * Title of the process.
     */
    protected string $title;

    /**
     * Description of the process.
     */
    protected string $description;

    /**
     * @inheritDoc
     */
    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct(self::PROCESS_DEFAULT);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return [
            'title'       => $this->title,
            'description' => $this->description
        ];
    }
}
