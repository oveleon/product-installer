<?php
use Oveleon\ProductInstaller\EventListener\LicenseConnector\Upload\UploadMatchProductsListener;

// Add Hook for LicenseConnector: Upload
$GLOBALS['PI_HOOKS']['matchProducts'][] = [
    UploadMatchProductsListener::class,
    'matchProducts'
];
