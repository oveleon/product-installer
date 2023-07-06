<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\MemberGroupModel;

/**
 * Validator class for validating the member group records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class MemberGroupValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return MemberGroupModel::getTable();
    }

    static public function getModel(): string
    {
        return MemberGroupModel::class;
    }
}
