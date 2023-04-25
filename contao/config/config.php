<?php
use Oveleon\ProductInstaller\EventListener\LicenseConnector\Upload\UploadMatchProductsListener;

// Add dca for product installer
$GLOBALS['BE_MOD']['system']['product_installer'] = [
    'tables'           => ['tl_product_installer'],
    'hideInNavigation' => true
];

// Add Hook for LicenseConnector: Upload
$GLOBALS['PI_HOOKS']['matchProducts'][] = [
    UploadMatchProductsListener::class,
    'matchProducts'
];
