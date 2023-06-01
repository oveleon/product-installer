<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

enum FormPromptType: string
{
    case TEXT = 'text';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
}
