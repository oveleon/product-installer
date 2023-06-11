<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;

class PageValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return PageModel::getTable();
    }

    static public function selectRootPage(array &$row, AbstractPromptImport $importer): ?array
    {
        // Skip the validator if it is not a root page or no pages exists
        if($row['type'] !== 'root' || PageModel::countAll() === 0)
        {
            return null;
        }

        if(null === ($rootPage = $importer->getPromptValue('rootPage')))
        {
            $pageCollection = [];

            $determinePageLevel = function ($page) use (&$pageCollection): int {

                if(\array_key_exists($page->pid, $pageCollection))
                {
                    $pageCollection[$page->id] = $pageCollection[$page->pid] + 1;

                    return $pageCollection[$page->id];
                }

                $pageCollection[$page->id] = 0;

                return $pageCollection[$page->id];
            };

            $values = [
                [
                    'value' => '0',
                    'text'  => $row['title'],
                    'group' => 'create',
                    'sorting' => 0,
                    'class' => 'root',
                    'level' => '0'
                ]
            ];

            if($pages = PageModel::findAll(['order' => 'id ASC, sorting ASC']))
            {
                $index = 0;

                foreach ($pages as $page)
                {
                    $values[] = [
                        'value'  => $page->id,
                        'text'   => $page->title,
                        'sorting'=> ++$index, // ToDo: Sorting is still wrong
                        'class'  => $page->type,
                        'info'   => $page->id,
                        'group'  => 'page',
                        'level'  => $determinePageLevel($page)
                    ];
                }
            }

            return [
                'rootPage' => [
                    $values,
                    FormPromptType::SELECT,
                    [
                        'class'   => 'pages',
                        'default' => ['0'],
                        'sortField' => ['level', 'sorting'],
                        'optgroupField' => 'group',
                        'optgroups' => [
                            [
                                'label' => 'Neue Seite anlegen',
                                'value' => 'create'
                            ],
                            [
                                'label' => 'In bestehende Seiten integrieren',
                                'value' => 'page'
                            ]
                        ]
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
    }

    static public function setLayoutConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        // Skip if the page has no own layout connection
        if(!$row['includeLayout'])
        {
            return null;
        }

        $pageId = $row['id'];
        $layoutId = $row['layout'];

        // Skip if we find a connection
        if(($connectedId = $importer->getConnection($layoutId, LayoutModel::getTable())) !== null)
        {
            // Disconnect layout
            if((int) $connectedId === 0)
            {
                $row['includeLayout'] = 0;
                $row['layout'] = 0;
            }
            // Set new layout id to the page
            else
            {
                $row['layout'] = $connectedId;
            }

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
        if(($connectedId = $importer->getPromptValue($connectionFieldName)) !== null)
        {
            // Disconnect layout
            if((int) $connectedId === 0)
            {
                $row['includeLayout'] = 0;
                $row['layout'] = 0;
            }
            // Set new layout id to the page
            else
            {
                $row['layout'] = $connectedId;
            }

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

            $values = [
                [
                    'value' => 0,
                    'text'  => 'Verknüfung aufheben',
                    'class' => 'disconnect',
                    'group' => 'actions'
                ]
            ];

            $optgroups = [
                [
                    'label' => 'Aktionen',
                    'value' => 'actions'
                ]
            ];

            if($layouts = LayoutModel::findAll())
            {
                $themeIds = [];

                foreach ($layouts as $layout)
                {
                    $values[] = [
                        'value' => $layout->id,
                        'text'  => $layout->name,
                        'class' => 'layout',
                        'info'  => $layout->id,
                        'group' => $layout->pid
                    ];

                    $themeIds[] = $layout->pid;
                }

                foreach (ThemeModel::findMultipleByIds($themeIds) ?? [] as $theme)
                {
                    $optgroups[] = [
                        'label' => 'Theme: ' . $theme->name,
                        'value' => $theme->id
                    ];
                }
            }

            $layoutStructure = $importer->getArchiveContentByTable(LayoutModel::getTable(), [
                'value' => $row['layout'],
                'field' => 'id'
            ]);

            return [
                $connectionFieldName => [
                    $values ?? [],
                    FormPromptType::SELECT,
                    [
                        'label'         => 'Layout → Seite zuordnen',
                        'description'   => 'Das Layout' . (($layoutStructure['name'] ?? false) ? ' "' . $layoutStructure['name'] . '"' : '') . ' wird von ein oder mehreren zu importierenden Seiten verwenden und muss neu zugeordnet werden. Ihre Auswahl wird für alle weiteren Seiten, welche auf dieses Layout referenzieren, übernommen.',
                        'explanation'   => [
                            'type'        => 'TABLE',
                            'description' => 'Beim Importieren einer oder mehrerer Seiten konnte ein zugewiesenes Layout nicht aufgelöst werden. Wählen Sie bitte ein Layout aus Ihrer Contao-Instanz um eine Verknüpfung zwischen diesen Seiten und einem Layout herzustellen.<br/><br/><b>Folgendes Layout wurde nicht importiert und benötigt eine Alternative:</b>',
                            'content'     => $layoutStructure ?? []
                        ],
                        'class'         => 'w50',
                        'optgroupField' => 'group',
                        'optgroups'     => $optgroups ?? []
                    ]
                ]
            ];
        }

        return null;
    }
}
