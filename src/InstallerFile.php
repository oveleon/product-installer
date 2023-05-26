<?php

namespace Oveleon\ProductInstaller;

use Contao\System;
use Symfony\Component\Filesystem\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class InstallerFile
{
    protected Filesystem $filesystem;
    protected string $root;
    protected string $path;
    protected string $filename;
    protected ?array $content;

    public function __construct(string $filename)
    {
        $this->filesystem = new Filesystem();
        $this->root = System::getContainer()->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . System::getContainer()->getParameter('product_installer.installer_path') . DIRECTORY_SEPARATOR;
        $this->path = $this->root . $filename;
        $this->filename = $filename;

        $this->createIfNotExists();
    }

    /**
     * Create the file if not exists.
     */
    protected function createIfNotExists(): void
    {
        if(!$this->filesystem->exists($this->path))
        {
            $this->filesystem->dumpFile($this->path, '');
        }

        $finder = new Finder();
        $finder->files()
               ->depth('== 0')
               ->in($this->root)
               ->name($this->filename);

        if(!$finder->hasResults())
        {
            throw new RuntimeException('Cannot find ' . $this->filename);
        }

        foreach ($finder as $file)
        {
            if($content = $file->getContents())
            {
                $this->content = json_decode($content, true);
                break;
            }

            $this->content = null;
            break;
        }
    }

    /**
     * Saves the file.
     */
    public function save(): void
    {
        $this->filesystem->touch($this->path);
        $this->filesystem->dumpFile($this->path, json_encode($this->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
