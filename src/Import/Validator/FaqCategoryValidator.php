<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\FaqCategoryModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the faq category records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FaqCategoryValidator implements ValidatorInterface
{
    use ValidatorTrait;

    static public function getTrigger(): string
    {
        return FaqCategoryModel::getTable();
    }

    static public function getModel(): string
    {
        return FaqCategoryModel::class;
    }
}
