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
    protected string $path;
    protected ?array $lock;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->root = System::getContainer()->getParameter('kernel.project_dir') . '/product-installer/';
        $this->path = $this->root . self::FILENAME;

        $this->createIfNotExists();
    }

    /**
     * Create the lock file if not exists.
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

    /**
     * Sets or updates a product.
     */
    public function setProduct(array $product): void
    {
        if(!$this->lock)
        {
            $this->lock = [
                'products' => []
            ];
        }

        $products = $this->lock['products'];

        if(!$this->hasProduct($product['hash']))
        {
            $products[] = $product;
        }
        else
        {
            foreach ($products ?? [] as $key => $p)
            {
                if($product['hash'] === $p['hash'])
                {
                    $products[$key] = $product;
                }
            }
        }

        $this->lock['products'] = $products;
    }

    /**
     * Check if a product exists by a given hash.
     */
    public function hasProduct($hash): bool
    {
        return (bool) $this->getProduct($hash);
    }

    /**
     * Return a product by a given hash.
     */
    public function getProduct($hash): ?array
    {
        if(!$this->lock)
        {
            return null;
        }

        foreach ($this->lock['products'] ?? [] as $product)
        {
            if($product['hash'] === $hash)
            {
                return $product;
            }
        }

        return null;
    }

    /**
     * Returns all products.
     */
    public function getInstalledProducts(?string $connector = null): ?array
    {
        if($this->lock)
        {
            if(null !== $connector)
            {
                $products = null;

                foreach ($this->lock['products'] as $product)
                {
                    if($product['connector'] === $connector)
                    {
                        $products[] = $product;
                    }
                }

                return $products;
            }

            return $this->lock['products'];
        }

        return null;
    }

    /**
     * Saves the lock file.
     */
    public function save(): void
    {
        $this->filesystem->touch($this->path);
        $this->filesystem->dumpFile($this->path, json_encode($this->lock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
