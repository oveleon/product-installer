<?php

namespace Oveleon\ProductInstaller\Import\Validator;

/**
 * Defines the mode of a validator:
 *
 * - BEFORE_IMPORT:     Validation takes place before import
 * - AFTER_IMPORT:      The validation takes place after the import
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum ValidatorMode: string
{
    case BEFORE_IMPORT = 'BEFORE';  // Can include prompts
    case AFTER_IMPORT = 'AFTER';    // Can not include prompts
}
