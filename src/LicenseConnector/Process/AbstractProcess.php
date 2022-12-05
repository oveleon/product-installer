<?php

namespace Oveleon\ProductInstaller\LicenseConnector\Process;

/**
 * The abstract class for new processes within a ProcessStep.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class AbstractProcess
{
    /**
     * Predefined steps.
     */
    const PROCESS_DEFAULT = 'DefaultProcess';

    /**
     * The step routes.
     */
    protected array $routes = [];

    /**
     * Name of the process instance.
     */
    public string $name;

    /**
     * Creates a new process.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Add a route to the process.
     */
    public function addRoute(string $name, string $route): self
    {
        $this->routes = [...$this->routes, ...[$name => $route]];

        return $this;
    }

    /**
     * Returns the process routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Returns process attributes
     */
    public function getAttributes(): array
    {
        return [];
    }
}
