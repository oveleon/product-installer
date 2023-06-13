<?php

namespace Oveleon\ProductInstaller\Import\Validator;

/**
 * Validator interface, which must be used for all validators.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
interface ValidatorInterface
{
    static public function getTrigger(): string;
}
