<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\FaqCategoryModel;

/**
 * Validator class for validating the faq category records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FaqCategoryValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return FaqCategoryModel::getTable();
    }

    static public function getModel(): string
    {
        return FaqCategoryModel::class;
    }
}
