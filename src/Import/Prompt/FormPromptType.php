<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

enum FormPromptType: string
{
    case TEXT = 'text';
    case SELECT = 'SELECT';
    case CHECKBOX = 'checkbox';
}
