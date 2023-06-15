<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

/**
 * Defines the different types of a prompt.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum ImportPromptType: string
{
    case FORM = 'FORM';
    case CONFIRM = 'CONFIRM';
}
