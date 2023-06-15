<?php

namespace Oveleon\ProductInstaller;

/**
 * Defines the different types of a product.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum ProductTaskType: string
{
    case REPO_IMPORT     = 'repo_import';
    case COMPOSER_UPDATE = 'composer_update';
    case CONTENT_PACKAGE = 'content_package';
    case MANAGER_PACKAGE = 'manager_package';
}
