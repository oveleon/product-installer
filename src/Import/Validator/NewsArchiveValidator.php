<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\NewsArchiveModel;

/**
 * Validator class for validating the news archive records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class NewsArchiveValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return NewsArchiveModel::getTable();
    }

    static public function getModel(): string
    {
        return NewsArchiveModel::class;
    }
}
