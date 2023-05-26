<?php

namespace Oveleon\ProductInstaller;

class InstallerLock extends InstallerFile
{
    public function __construct()
    {
        parent::__construct('installer-lock.json');
    }

    /**
     * Sets or updates a product.
     */
    public function setProduct(array $product): void
    {
        if(!$this->content)
        {
            $this->content = [
                'products' => []
            ];
        }

        $products = $this->content['products'];

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

        $this->content['products'] = $products;
    }

    /**
     * Removes a product.
     */
    public function removeProduct($hash): void
    {
        if(!$this->content || !$this->hasProduct($hash))
        {
            return;
        }

        $products = [];

        foreach ($this->content['products'] ?? [] as $key => $p)
        {
            if($hash === $p['hash'])
            {
                continue;
            }

            $products[] = $p;
        }


        $this->content['products'] = $products;
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
        if(!$this->content)
        {
            return null;
        }

        foreach ($this->content['products'] ?? [] as $product)
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
        if($this->content)
        {
            if(null !== $connector)
            {
                $products = null;

                foreach ($this->content['products'] as $product)
                {
                    if($product['connector'] === $connector)
                    {
                        $products[] = $product;
                    }
                }

                return $products;
            }

            return $this->content['products'];
        }

        return null;
    }
}
