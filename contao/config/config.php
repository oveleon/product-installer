<?php

use Contao\PageModel;
use Contao\LayoutModel;
use Contao\ThemeModel;
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
Validator::addValidator(PageModel::getTable(), static function (array &$row, AbstractPromptImport $importer): ?array
{
    // Skip the validator if it is not a root page or no pages exists
    if($row['type'] !== 'root' || PageModel::countAll() === 0)
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

            // Add id connection for child tables
            $importer->addConnection($row['id'], $rootPage);
        }
    }

    return null;
});

// Set page-layout connections
Validator::addValidator(PageModel::getTable(), static function (array &$row, AbstractPromptImport $importer): ?array
{
    // Skip if the page has no own layout connection
    if(!$row['includeLayout'])
    {
        return null;
    }

    $pageId = $row['id'];
    $layoutId = $row['layout'];

    // Skip if we find a connection
    if($connectedId = $importer->getConnection($layoutId, LayoutModel::getTable()))
    {
        // Set new layout id to the page
        $row['layout'] = $connectedId;

        return null;
    }

    $skip = [];
    $connectionFieldName = 'pageLayout_' . $pageId;

    // Check if we got a prompt response and should skip layout prompts of the same ID
    if($importer->getFlashConnection($layoutId, 'layout_page_connection'))
    {
        $skip[] = $pageId;
    }

    // Check if we have already received a user decision
    if($connectedId = (int) $importer->getPromptValue($connectionFieldName))
    {
        // Set new layout id to the page
        $row['layout'] = $connectedId;

        // Add id connection for child tables
        $importer->addConnection($layoutId, $connectedId, LayoutModel::getTable());
    }
    else
    {
        if(\in_array($pageId, $skip))
        {
            return null;
        }

        $importer->addFlashConnection($layoutId, $pageId, 'layout_page_connection');

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
            $connectionFieldName => [
                $values ?? [],
                FormPromptType::SELECT,
                [
                    'label'       => 'Seitenlayout zuordnen',
                    'description' => 'Das Seitenlayout für die Seite "' . $row['title'] . '" (' . $row['id'] . ') muss neu zugeordnet werden.',
                    'info'        => 'Beim Importieren der Seite "' . $row['title'] . '" (' . $row['id'] . ') konnte ein zugewiesenes Layout nicht aufgelöst werden. Wählen Sie bitte ein Layout aus Ihrer Contao-Instanz um eine Verknüpfung zwischen Seite und Layout herzustellen.<br/><br/><b>Verknüpfung für alle weiteren Layouts mit dieser ID anwenden:</b><br/>Bei Auswahl dieser Einstellung, wird die zugewiesene Layout-ID für alle weiteren Seiten verwendet, wo die Verknüpfung auf dasselbe nicht zuweisbare Layout zeigen.',
                    'class'       => 'w50'
                ]
            ]
        ];
    }

    return null;
});

/*Validator::addValidator(PageModel::getTable(), static function (array &$row, AbstractPromptImport $importer): ?array
{
    $pageId = $row['id'];
    $layoutId = $row['layout'];

    // Skip if the page has no own layout connection
    if(!$row['includeLayout'])
    {
        return null;
    }

    // Skip if we find a connection
    if($connectedId = $importer->getConnection($layoutId, LayoutModel::getTable()))
    {
        // Set new layout id to the page
        $row['layout'] = $connectedId;
        return null;
    }

    $skip = [];

    $pageLayoutId = 'pageLayout_' . $row['id'];
    $rememberLayoutId = 'rememberLayout_' . $row['id'];

    // Check if we got a prompt response and should skip layout prompts of the same ID
    if((int) $importer->getPromptValue($rememberLayoutId) || $importer->getFlashConnection($layoutId, 'layout_page_connection'))
    {
        $skip[] = $pageId;
    }

    // Check if we have already received a user decision
    if($connectedId = (int) $importer->getPromptValue($pageLayoutId))
    {
        // Set new layout id to the page
        $row['layout'] = $connectedId;

        // Add id connection for child tables
        $importer->addConnection($layoutId, $connectedId, LayoutModel::getTable());
    }
    else
    {
        if(\in_array($pageId, $skip))
        {
            return null;
        }

        $importer->addFlashConnection($layoutId, $pageId, 'layout_page_connection');

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

        $fields = [$pageLayoutId => [
                $values ?? [],
                FormPromptType::SELECT,
                [
                    'label'       => 'Seitenlayout zuordnen',
                    'description' => 'Das Seitenlayout für die Seite "' . $row['title'] . '" (' . $row['id'] . ') muss neu zugeordnet werden.',
                    'info'        => 'Beim Importieren der Seite "' . $row['title'] . '" (' . $row['id'] . ') konnte ein zugewiesenes Layout nicht aufgelöst werden. Wählen Sie bitte ein Layout aus Ihrer Contao-Instanz um eine Verknüpfung zwischen Seite und Layout herzustellen.<br/><br/><b>Verknüpfung für alle weiteren Layouts mit dieser ID anwenden:</b><br/>Bei Auswahl dieser Einstellung, wird die zugewiesene Layout-ID für alle weiteren Seiten verwendet, wo die Verknüpfung auf dasselbe nicht zuweisbare Layout zeigen.',
                    'class'       => 'w50'
                ]
            ]
        ];

        if($importer->getPromptValue($rememberLayoutId) !== "")
        {
            $fields[$rememberLayoutId] = [
                [
                    [
                        'name'    => $rememberLayoutId,
                        'value'   => $pageId,
                        'text'    => 'Zuordnung für alle weiteren Layouts mit dieser ID anwenden.',
                        'options' => [
                            'checked' => true
                        ]
                    ]
                ],
                FormPromptType::CHECKBOX,
                [
                    'class'       => 'w50 m12'
                ]
            ];
        }

        return $fields;
    }

    return null;
});*/

// Set layout-theme connections
/*Validator::addValidator(LayoutModel::getTable(), static function (array &$row, AbstractPromptImport $importer): ?array
{
    $themeId = $row['pid'];
    $layoutId = $row['id'];

    // Skip if we find a connection
    if($importer->getConnection($themeId, ThemeModel::getTable()))
    {
        return null;
    }

    $skip = [];

    $layoutThemeId = 'layoutTheme_' . $row['id'];
    $rememberThemeId = 'rememberTheme_' . $row['id'];

    // Check if we got a prompt response and should skip theme prompts of the same ID
    if(
        (
            (int) $importer->getPromptValue($rememberThemeId) &&
            (int) $importer->getPromptValue($layoutThemeId)
        ) ||
        $importer->getFlashConnection($themeId, 'theme_layout_connection')
    ){
        $skip[] = $themeId;
    }

    // Check if we have already received a user decision
    if($connectedId = (int) $importer->getPromptValue($layoutThemeId))
    {
        // Add id connection for child tables
        $importer->addConnection($layoutId, $connectedId, ThemeModel::getTable());
    }

    if(!(int) $importer->getPromptValue($rememberThemeId))
    {
        if(\in_array($themeId, $skip))
        {
            return null;
        }

        // Add a flash connection to display prompts for the same connections only once for the first time
        $importer->addFlashConnection($themeId, $layoutId, 'theme_layout_connection');

        if($themes = ThemeModel::findAll())
        {
            foreach ($themes as $theme)
            {
                $values[] = [
                    'value' => $theme->id,
                    'text'  => $theme->name
                ];
            }
        }

        $fields = [$layoutThemeId => [
                $values ?? [],
                FormPromptType::SELECT,
                [
                    'label'       => 'Layout zuordnen',
                    'description' => 'Das Layout "' . $row['name'] . '" (' . $row['id'] . ') muss einem Theme zugeordnet werden.',
                    'info'        => 'Beim Importieren der Seite "' . $row['name'] . '" (' . $row['id'] . ') konnte ein zugewiesenes Layout nicht aufgelöst werden. Wählen Sie bitte ein Layout aus Ihrer Contao-Instanz um eine Verknüpfung zwischen Seite und Layout herzustellen.<br/><br/><b>Verknüpfung für alle weiteren Layouts mit dieser ID anwenden:</b><br/>Bei Auswahl dieser Einstellung, wird die zugewiesene Layout-ID für alle weiteren Seiten verwendet, wo die Verknüpfung auf dasselbe nicht zuweisbare Layout zeigen.',
                    'class'       => 'w50'
                ]
            ]
        ];

        if($importer->getPromptValue($rememberThemeId) !== "" || !$connectedId)
        {
            $fields[$rememberThemeId] = [
                [
                    [
                        'name'    => $rememberThemeId,
                        'value'   => $themeId,
                        'text'    => 'Zuordnung für alle weiteren Layouts mit dieser Theme-ID anwenden.',
                        'options' => [
                            'checked' => true
                        ]
                    ]
                ],
                FormPromptType::CHECKBOX,
                [
                    'class'       => 'w50 m12'
                ]
            ];
        }

        return $fields;
    }

    return null;
});*/

Validator::addValidator(LayoutModel::getTable(), static function (array &$row, AbstractPromptImport $importer): ?array
{
    $parentId = $row['pid'];
    $id       = $row['id'];

    // Skip if we find a connection
    if($importer->getConnection($parentId, ThemeModel::getTable()))
    {
        return null;
    }

    $skip = [];
    $connectionFieldName = 'connection_' . $id;

    // Check if we got a prompt response and should skip prompts of the same ID
    if($importer->getFlashConnection($parentId, 'theme_layout_connection'))
    {
        $skip[] = $parentId;
    }

    // Check if we have already received a user decision
    if($connectedId = (int) $importer->getPromptValue($connectionFieldName))
    {
        // Add id connection for child row
        $importer->addConnection($parentId, $connectedId, ThemeModel::getTable());
    }
    else
    {
        if(\in_array($parentId, $skip))
        {
            return null;
        }

        // Add a flash connection to display prompts for the same connections only once
        $importer->addFlashConnection($parentId, $id, 'theme_layout_connection');

        if($themes = ThemeModel::findAll())
        {
            foreach ($themes as $theme)
            {
                $values[] = [
                    'value' => $theme->id,
                    'text'  => $theme->name
                ];
            }
        }

        return [
            $connectionFieldName => [
                $values ?? [],
                FormPromptType::SELECT,
                [
                    'label'       => 'Layout zuordnen',
                    'description' => 'Das Layout "' . $row['name'] . '" (' . $row['id'] . ') muss einem Theme zugeordnet werden.',
                    'info'        => 'Beim Importieren der Seite "' . $row['name'] . '" (' . $row['id'] . ') konnte ein zugewiesenes Layout nicht aufgelöst werden. Wählen Sie bitte ein Layout aus Ihrer Contao-Instanz um eine Verknüpfung zwischen Seite und Layout herzustellen.<br/><br/><b>Verknüpfung für alle weiteren Layouts mit dieser ID anwenden:</b><br/>Bei Auswahl dieser Einstellung, wird die zugewiesene Layout-ID für alle weiteren Seiten verwendet, wo die Verknüpfung auf dasselbe nicht zuweisbare Layout zeigen.',
                    'class'       => 'w50'
                ]
            ]
        ];
    }

    return null;
});
