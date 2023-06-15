<?php

namespace Oveleon\ProductInstaller\Import;

/**
 * Defines the state of an import:
 *
 * - INIT:      First call of a data set to be imported
 * - SKIP:      The record is or was skipped
 * - PROMPT:    The record requires user input
 * - IMPORT:    The record will or can be imported
 * - FINISH:    The record was imported
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum ImportStateType: string
{
    case INIT    = 'INIT';
    case SKIP    = 'SKIP';
    case PROMPT  = 'PROMPT';
    case IMPORT  = 'IMPORT';
    case FINISH  = 'FINISH';
}
