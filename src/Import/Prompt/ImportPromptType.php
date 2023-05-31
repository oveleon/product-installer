<?php

namespace Oveleon\ProductInstaller\Import\Prompt;

enum ImportPromptType: string
{
    case FORM = 'FORM';
    case CONFIRM = 'CONFIRM';
}
