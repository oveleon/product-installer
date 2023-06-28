<?php

namespace Oveleon\ProductInstaller\Import\Validator;

/**
 * Defines the mode of a validator:
 *
 * - BEFORE_IMPORT_ROW: The validation takes place before the import of a table and is called for each row (possible return of prompts).
 * - AFTER_IMPORT_ROW:  The validation takes place after the import of a table and is called for each row (no return of prompts).
 * - AFTER_IMPORT:      The validation takes place after the import of a table. Unlike AFTER_IMPORT_ROW mode, this mode is also invoked if, for example, all records have been skipped (no return of prompts).
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum ValidatorMode: string
{
    case BEFORE_IMPORT_ROW  = 'BEFORE_ROW';
    case AFTER_IMPORT_ROW   = 'AFTER_ROW';
    case AFTER_IMPORT       = 'AFTER';
}
