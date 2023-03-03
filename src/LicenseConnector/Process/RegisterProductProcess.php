<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Process;

/**
 * The process class representing the component of the register products process.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 *
 * Attributes:
 * @property $title
 * @property $description
 */
class RegisterProductProcess extends AbstractProcess
{
    /**
     * @inheritDoc
     */
    public function __construct(string $title = '', string $description = '')
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct(self::PROCESS_REGISTER_PRODUCTS);
    }
}
