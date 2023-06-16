<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\FormModel;

/**
 * Validator class for validating the form records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FormValidator implements ValidatorInterface
{
    public static function getTrigger(): string
    {
        return FormModel::getTable();
    }

    static public function getModel(): string
    {
        return FormModel::class;
    }
}
