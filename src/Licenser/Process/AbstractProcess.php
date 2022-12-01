<?php

namespace Oveleon\ProductInstaller\Licenser\Process;

/**
 * The abstract class for new processes within a ProcessStep.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class AbstractProcess
{
    /**
     * The step routes.
     */
    protected array $routes = [];

    /**
     * Title of the process.
     */
    protected string $title;

    /**
     * Description of the process.
     */
    protected string $description;

    /**
     * Creates a new process.
     */
    public function __construct(string $title, string $description)
    {
        $this->title = $title;
        $this->description = $description;
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
     * Returns the process routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
