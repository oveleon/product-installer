<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Process;

/**
 * The process class representing the component of the download process.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 *
 * Attributes:
 * @property $title
 * @property $description
 */
class DownloadProcess extends AbstractProcess
{
    /**
     * @inheritDoc
     */
    public function __construct(string $title = '', string $description = '')
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct(self::PROCESS_DOWNLOAD);
    }
}
