<?php

namespace Oveleon\ProductInstaller\Licenser;

use Oveleon\ProductInstaller\Licenser\Step\AbstractStep;

/**
 * The abstract class for new licensers.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class AbstractLicenser
{
    /**
     * The licenser steps.
     */
    protected array $steps = [];

    /**
     * Create licenser
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
     * Returns a licenser configuration:
     *
     * image:       /path-to-custom-image.svg
     * title:       Title of the Product-Licenser
     * description: Description of the Product-Licenser
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
