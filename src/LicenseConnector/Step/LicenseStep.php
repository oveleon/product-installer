<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Step;

/**
 * The step class representing the component of the license step.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 *
 * Attributes:
 * @property $title
 * @property $description
 */
class LicenseStep extends AbstractStep
{
    /**
     * @inheritDoc
     */
    public function __construct(string $title = '', string $description = '')
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct(self::STEP_LICENSE);
    }
}
