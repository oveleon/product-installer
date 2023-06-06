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
            [
                'value' => '0',
                'text'  => 'Neue Seite anlegen (' . $row['title'] . ')'
            ]
        ];

        if($pages = PageModel::findAll())
        {
            foreach ($pages as $page)
            {
                $values[] = [
                    'value' => $page->id,
                    'text'  => $page->title
                ];
            }
        }

        return [
            'rootPage' => [
                $values,
                FormPromptType::SELECT,
                [
                    'default' => ['0']
                ]
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
Validator::addValidator('tl_page', static function (array &$row, AbstractPromptImport $importer): ?array
{
    // ToDo: Remember the field layout id to skip questions from same type

    // Skip if the page has no own layout connection
    if(!$row['includeLayout'])
    {
        return null;
    }

    if($connectedId = $importer->getConnection($row['layout'], 'tl_layout'))
    {
        $row['layout'] = $connectedId;
    }
    else
    {
        if($layouts = LayoutModel::findAll())
        {
            foreach ($layouts as $layout)
            {
                $values[] = [
                    'value' => $layout->id,
                    'text'  => $layout->name
                ];
            }
        }

        return [
            'pageLayout_' . $row['id'] => [
                $values ?? [],
                FormPromptType::SELECT,
                [
                    'label'       => 'Seitenlayout zuordnen',
                    'description' => 'Das Seitenlayout (' . $row['layout'] . ') für die Seite (' . $row['id'] . ') muss neu verknüpft werden.',
                    'info'        => 'Beim Importieren einer Seite konnte ein zugewiesenes Layout nicht aufgelöst werden. Wählen Sie bitte ein Layout aus Ihrer Contao-Instanz um eine Verknüpfung zwischen Seite und Layout herzustellen.<br/><br/><b>Verknüpfung für alle weiteren Layouts mit dieser ID anwenden:</b><br/>Bei Auswahl dieser Einstellung, wird die zugewiesene Layout-ID für alle weiteren Seiten verwendet, wo die Verknüpfung auf dasselbe nicht zuweisbare Layout zeigen.',
                    'class'       => 'w50'
                ]
            ],
            'rememberLayout_' . $row['id'] => [
                [
                    [
                        'value'   => 1,
                        'text'    => 'Verknüpfung für alle weiteren Layouts mit dieser ID anwenden.',
                        'options' => [
                            'checked' => true
                        ]
                    ]
                ],
                FormPromptType::CHECKBOX,
                [
                    'class'       => 'w50 m12'
                ]
            ]
        ];
    }

    return null;
});
