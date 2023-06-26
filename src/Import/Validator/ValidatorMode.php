<?php

namespace Oveleon\ProductInstaller\Import\Validator;

/**
 * Defines the mode of a validator:
 *
 * - BEFORE_IMPORT:     Validation takes place before import (possible return of prompts)
 * - AFTER_IMPORT:      The validation takes place after the import (no return of prompts)
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum ValidatorMode: string
{
    case BEFORE_IMPORT      = 'BEFORE';
    case AFTER_IMPORT       = 'AFTER';
}
