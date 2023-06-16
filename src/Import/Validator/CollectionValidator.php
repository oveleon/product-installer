<?php

namespace Oveleon\ProductInstaller\Import\Validator;

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
     * Treats the connection to a redirect page.
     */
    static function setJumpToPageConnection(array &$row, AbstractPromptImport $importer, string|Model $model): ?array
    {
        return self::setFieldPageConnection($model, 'jumpTo', $row, $importer);
    }
}
