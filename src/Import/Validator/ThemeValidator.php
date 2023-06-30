<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ThemeModel;

/**
 * Validator class for validating theme records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ThemeValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ThemeModel::getTable();
    }

    static public function getModel(): string
    {
        return ThemeModel::class;
    }

    
}
