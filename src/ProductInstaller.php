<?php

declare(strict_types=1);

namespace Oveleon\ProductInstaller;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ProductInstaller extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
