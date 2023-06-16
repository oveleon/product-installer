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
    static public function getTrigger(): string;
    static public function getModel(): string|Model;
}
