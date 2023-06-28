<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Model;

/**
 * Validator interface, which must be used for all validators.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
interface ValidatorInterface
{
    public static function getTrigger(): string;
    public static function getModel(): string|Model;
}
