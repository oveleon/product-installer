<?php

namespace Oveleon\ProductInstaller;

use Contao\System;
use Symfony\Component\Filesystem\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InstallerLock
{
    const FILENAME = 'installer-lock.json';

    protected Filesystem $filesystem;
    protected string $root;
    protected ?array $lock;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->root = System::getContainer()->getParameter('kernel.project_dir');

        $this->createIfNotExists();
    }

    protected function createIfNotExists(): void
    {
        $path = $this->root . '/' . self::FILENAME;

        if(!$this->filesystem->exists($path))
        {
            $this->filesystem->touch($path);
        }

        $finder = new Finder();
        $finder->files()
               ->depth('== 0')
               ->in($this->root)
               ->name(self::FILENAME);

        if(!$finder->hasResults())
        {
            throw new RuntimeException('Cannot find ' . self::FILENAME);
        }

        foreach ($finder as $file)
        {
            if($content = $file->getContents())
            {
                $this->lock = json_decode($content, true);
                break;
            }

            $this->lock = null;
            break;
        }
    }

    public function getInstalledProducts(): ?array
    {
        if($this->lock)
        {
            return $this->lock['products'];
        }

        return null;
    }
}
