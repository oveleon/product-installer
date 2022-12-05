<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Step;

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
    const STEP_LICENSE = 'LicenseStep';
    const STEP_PRODUCT = 'ProductStep';
    const STEP_PROCESS = 'ProcessStep';
    const STEP_CUSTOM  = 'CustomStep';

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
     * Add a route to the step.
     */
    public function addRoute(string $name, string $route): self
    {
        $this->routes = [...$this->routes, ...[$name => $route]];

        return $this;
    }

    /**
     * Returns the step routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Returns step attributes.
     */
    public function getAttributes(): array
    {
        return [];
    }
}
