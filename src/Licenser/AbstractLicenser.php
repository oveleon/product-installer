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
     * Sets the steps to be traversed:
     *
     * $this->addSteps(
     *   new LicenseStep()
     *   new ProductStep()
     *   ...
     * );
     */
    abstract function steps(): void;

    /**
     * Returns a licenser configuration:
     *
     * icon:        /path-to-custom-icon.svg
     * title:       Title of the Product-Licenser
     * description: Description of the Product-Licenser
     */
    abstract function config(): array;

    /**
     * Adds one or more step.
     */
    protected function addSteps(AbstractStep ...$step): self
    {
        $this->steps = [...$this->steps, ...$step];

        return $this;
    }
}
