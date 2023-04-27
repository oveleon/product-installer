<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

enum TaskAction: string
{
    case COMPOSER_UPDATE        = 'composer/update';
    case COMPOSER_INSTALL       = 'composer/install';
    case COMPOSER_DUMP_AUTOLOAD = 'composer/dump-autoload';
    case CONTAO_INSTALL         = 'contao/install';
    case CONTAO_REBUILD_CACHE   = 'contao/rebuild-cache';
    case CONTAO_CLEAR_CACHE     = 'contao/clear-cache';
    case MANAGER_SELF_UPDATE    = 'manager/self-update';
}
