<?php

namespace Oveleon\ProductInstaller;

/**
 * Class to edit the local JSON file setup-lock.json.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class SetupLock extends InstallerFile
{
    /**
     * Current setup scope.
     */
    protected string $scope = '_';

    public function __construct()
    {
        parent::__construct('setup-lock.json');
    }

    /**
     * Set scope.
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * Returns the content of a scope.
     */
    public function getScope(string $scope, null|string|array $default = null): ?array
    {
        return $this->content[$scope] ?? $default;
    }

    /**
     * Removes a scope.
     */
    public function removeScope(string $scope): void
    {
        if($this->getScope($scope))
        {
            unset($this->content[$scope]);
        }
    }

    /**
     * Set key-value pair.
     */
    public function set(string $key, string|array $value): void
    {
        if(empty($this->content[$this->scope]))
        {
            $this->content[$this->scope] = [];
        }

        $this->content[$this->scope][$key] = $value;
    }

    /**
     * Get value by key.
     */
    public function get(string $key, null|string|array $default = null): null|string|array
    {
        return $this->content[$this->scope][$key] ?? $default;
    }

    /**
     * Remove by key.
     */
    public function remove(string $key): void
    {
        unset($this->content[$this->scope][$key]);
    }
}
