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
    const PROCESS_API = 'ApiProcess';
    const PROCESS_CM  = 'ContaoManagerProcess';
    const PROCESS_REGISTER_PRODUCTS = 'RegisterProductProcess';

    /**
     * The process routes.
     */
    protected array $routes = [];

    /**
     * The process attributes.
     */
    protected array $attributes = [];

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
     * Set attributes.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
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
        return $this->attributes;
    }
}
