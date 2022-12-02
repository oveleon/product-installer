<?php

namespace Oveleon\ProductInstaller\Licenser\Step;

/**
 * The abstract class for new steps.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class AbstractStep
{
    /**
     * Predefined steps.
     */
    const STEP_LICENSE = 'license';
    const STEP_PRODUCT = 'product';
    const STEP_PROCESS = 'process';
    const STEP_CUSTOM  = 'custom';

    /**
     * Name of the step.
     */
    public string $name;

    /**
     * The step routes.
     */
    protected array $routes = [];

    /**
     * Create a new step.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Adds one or more steps to a route.
     */
    public function addRoutes(string ...$route): self
    {
        $this->routes = [...$this->routes, ...$route];

        return $this;
    }

    /**
     * Returns the step routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
