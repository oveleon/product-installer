<?php

use Contao\PageModel;
use Contao\LayoutModel;
use Oveleon\ProductInstaller\EventListener\LicenseConnector\Upload\UploadMatchProductsListener;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\Validator;

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

/**
 * Add default import validators
 */
// Select a root page to import
Validator::addValidator('tl_page', static function (array &$row, AbstractPromptImport $importer): ?array
{
    // Skip the validator if it is not a root page
    if($row['type'] !== 'root')
    {
        return null;
    }

    if(null === ($rootPage = $importer->getPromptValue('rootPage')))
    {
        $values = [
            '0' => 'Neue Seite anlegen (' . $row['title'] . ')'
        ];

        if($pages = PageModel::findAll())
        {
            $values = $values + array_combine(
                $pages->fetchEach('id'),
                $pages->fetchEach('title')
            );
        }

        return [
            'rootPage' => [
                $values,
                FormPromptType::SELECT
            ]
        ];
    }
    else
    {
        // If another root page was selected, the given root page won't be imported
        if($rootPage !== '0')
        {
            $row['_skip'] = true;
        }

        // Add id connection for child tables
        $importer->addConnection($row['id'], $rootPage);
    }

    return null;
});

// Set layout
/*Validator::addValidator('tl_page', static function (array &$row, AbstractPromptImport $importer): ?array
{
    // Skip if the page has no own layout connection
    if(!$row['includeLayout'])
    {
        return null;
    }

    if($connectedId = $importer->getConnection($row['includeLayout'], 'tl_layout'))
    {
        $row['layout'] = $connectedId;
    }
    else
    {
        if($layouts = LayoutModel::findAll())
        {
            $values = array_combine(
                $layouts->fetchEach('id'),
                $layouts->fetchEach('title')
            );
        }

        // ToDo: Multiple concat page id?
        return [
            'pageLayout' => [
                $values,
                FormPromptType::SELECT
            ]
        ];
    }

    return null;
});*/
