<?php

namespace Oveleon\ProductInstaller\Controller\API\Composer;

use Contao\System;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class Composer
{
    protected ?object $composer = null;

    /**
     * Adds a repository to composer file.
     */
    public function addRepository(string $type, string $url): self
    {
        if($this->load())
        {
            $repository = new \stdClass();
            $repository->type = $type;
            $repository->url = $url;

            $this->composer->repositories[] = $repository;
        }

        return $this;
    }

    /**
     * Check if a repository already exists.
     */
    public function hasRepository(string $url): bool
    {
        if($this->load())
        {
            foreach ($this->composer->repositories as $repository)
            {
                if($repository->url === $url)
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Load the project composer file
     *
     * @throw FileException
     */
    public function load(): bool
    {
        if($this->composer)
        {
            return true;
        }

        $root = System::getContainer()->getParameter('kernel.project_dir');
        $finder = new Finder();
        $finder
            ->depth('== 0')
            ->files()
            ->name('composer.json')
            ->in($root);

        if($finder->hasResults())
        {
            foreach ($finder as $file)
            {
                $this->composer = json_decode($file->getContents());
                return true;
            }
        }

        throw new FileException('The composer.json file cannot be loaded.');
    }

    /**
     * Overwrites the composer.json file
     */
    public function save(): void
    {
        $this->load();

        $root = System::getContainer()->getParameter('kernel.project_dir');
        $path = $root . '/composer.json';

        $filesystem = new Filesystem();
        $filesystem->touch($path);
        $filesystem->dumpFile($path, json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
