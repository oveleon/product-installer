<?php

namespace Oveleon\ProductInstaller\LicenseConnector;

use Oveleon\ProductInstaller\LicenseConnector\Step\AbstractStep;

/**
 * The abstract class for new license connectors.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class AbstractLicenseConnector
{
    /**
     * The license connector steps.
     */
    protected array $steps = [];

    /**
     * Create license connector
     */
    public function __construct()
    {
        $this->setSteps();
    }

    /**
     * Sets the steps to be traversed:
     *
     * $this->addSteps(
     *   new LicenseStep()
     *   new ProductStep()
     *   ...
     * );
     */
    abstract protected function setSteps(): void;

    /**
     * Returns a license connector configuration:
     *
     * image:       /path-to-custom-image.svg
     * title:       Title of the License connector
     * description: Description of the license connector
     */
    abstract public function getConfig(): array;

    /**
     * Return all steps
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Adds one or more step.
     */
    protected function addSteps(AbstractStep ...$step): self
    {
        $this->steps = [...$this->steps, ...$step];

        return $this;
    }
}
