<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\MemberGroupModel;
use Contao\Model;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the various records during and after import.
 * Unlike other validators, the Collection Validator handles several models at once and thus provides one and the same functionality for all of them.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class CollectionValidator
{
    use ValidatorTrait;

    /**
     * Handles the connection to a redirect page.
     */
    static function setJumpToPageConnection(array &$row, AbstractPromptImport $importer, string|Model $model): ?array
    {
        switch(true)
        {
            case $model instanceof MemberGroupModel:
                if(!$row['redirect'])
                {
                    return null;
                }

            default:
                if(!$row['jumpTo'])
                {
                    return null;
                }
        }

        return self::setFieldPageConnection($model, 'jumpTo', $row, $importer);
    }
}
