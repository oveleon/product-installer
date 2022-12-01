<?php

namespace Oveleon\ProductInstaller\Licenser\Step;

use Oveleon\ProductInstaller\Licenser\Process\AbstractProcess;

/**
 * The step class representing the component of the process step.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ProcessStep extends AbstractStep
{
    /**
     * The processes of the step.
     */
    protected array $processes = [];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(self::STEP_PROCESS);
    }

    /**
     * Adds processes to the step.
     */
    public function addProcesses(AbstractProcess ...$process): self
    {
        $this->processes = [...$this->processes, ...$process];

        return $this;
    }

    /**
     * Returns the step processes.
     */
    public function getProcesses(): array
    {
        return $this->processes;
    }
}
