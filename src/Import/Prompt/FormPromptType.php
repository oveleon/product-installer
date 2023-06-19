<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

/**
 * Defines the different types of a form prompt.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum FormPromptType: string
{
    case TEXT = 'text';
    case FILE = 'file';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
}
