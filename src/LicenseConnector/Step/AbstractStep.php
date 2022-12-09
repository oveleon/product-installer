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
     * ToDo:
     * - Allow creating own actions and behaviours
     * - Create custom step
     */

    /**
     * Predefined steps.
     */
    const STEP_LICENSE        = 'LicenseStep';
    const STEP_PRODUCT        = 'ProductStep';
    const STEP_PROCESS        = 'ProcessStep';
    const STEP_CUSTOM         = 'CustomStep';
    const STEP_CONTAO_MANAGER = 'ContaoManagerStep';

    /**
     * Name of the step.
     */
    public string $name;

    /**
     * The step routes.
     */
    protected array $routes = [];

    /**
     * The step attributes.
     */
    protected array $attributes = [];

    /**
     * Create a new step.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Set custom attributes.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
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
        return $this->attributes;
    }
}
