<?php

namespace Oveleon\ProductInstaller\Import;

enum ImportStateType: string
{
    case INIT    = 'INIT';
    case SKIP    = 'SKIP';
    case PROMPT  = 'PROMPT';
    case IMPORT  = 'IMPORT';
    case FINISH  = 'FINISH';
}
