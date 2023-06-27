<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\ImportStateType;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;
use Oveleon\ProductInstaller\Util\PageUtil;

/**
 * Validator class for validating the page records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class PageValidator implements ValidatorInterface
{
    use ValidatorTrait;

    static public function getTrigger(): string
    {
        return PageModel::getTable();
    }

    static public function getModel(): string
    {
        return PageModel::class;
    }

    /**
     * Handles the selection of a page root.
     *
     * @category BEFORE_IMPORT
     */
    static public function selectRootPage(array &$row, TableImport $importer): ?array
    {
        // Skip the validator if it is not a root page or no pages exists
        if($row['type'] !== 'root' || PageModel::countAll() === 0)
        {
            return null;
        }

        if(null === ($rootPage = $importer->getPromptValue('rootPage')))
        {
            $translator = Controller::getContainer()->get('translator');

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

            /** @var PageUtil $pageUtil */
            $pageUtil = System::getContainer()
                ->get("Oveleon\ProductInstaller\Util\PageUtil")
                ->setPages();

            foreach ($pageUtil->getPagesFlat() as $page)
            {
                $values[] = [
                    'value'  => $page['id'],
                    'text'   => $page['title'],
                    'class'  => $page['type'],
                    'info'   => $page['id'],
                    'group'  => 'page',
                    'level'  => $page['_level']
                ];
            }

            return [
                'rootPage' => [
                    $values,
                    FormPromptType::SELECT,
                    [
                        'class'   => 'pages',
                        'default' => ['0'],
                        'optgroupField' => 'group',
                        'optgroups' => [
                            [
                                'label' => $translator->trans('setup.prompt.page.root.create', [], 'setup'),
                                'value' => 'create'
                            ],
                            [
                                'label' => $translator->trans('setup.prompt.page.root.extend', [], 'setup'),
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
            /*if($rootPage !== '0')
            {
                $row['_skip'] = true;

                // Add id connection for child tables
                $importer->addConnection($row['id'], $rootPage);
            }*/

            if($rootPage !== '0')
            {
                // Overriding the page root check during import
                $row['_keep'] = true;

                // Add id connection for child tables
                $importer->addConnection(0, $rootPage);
            }
        }

        return null;
    }

    /**
     * Handles the relationship between a page and its layout.
     *
     * @category BEFORE_IMPORT
     */
    static public function setLayoutConnection(array &$row, TableImport $importer): ?array
    {
        // Skip if the page has no own layout connection
        if(!$row['includeLayout'])
        {
            return null;
        }

        // In empty Contao instances, it will happen at this point that no layouts exist yet. In this case, the row
        // that references a layout is stored as a new layout-validator in order to be able to establish the connection
        // after importing the layouts.
        //
        // This is done under the following conditions:
        //  1. The table has not yet been imported
        //  2. The table will be imported
        //
        // If this is not the case, layouts must already exist in the system, which must be connected by the user.
        if(
            $importer->getState(LayoutModel::getTable()) !== ImportStateType::FINISH &&
            $importer->willBeImported(LayoutModel::getTable())
        )
        {
            // Add page-layout connection to retrieve them in the new validator (layoutId, pageId). See method `connectLayout` for more information.
            $importer->addConnection($row['layout'], $row['id'], '_connectLayout');

            // Add persist layout validator
            $importer->addLifecycleValidator('connectLayout_' . $row['layout'], LayoutModel::getTable(), [self::class, 'connectLayout'], ValidatorMode::AFTER_IMPORT);

            return null;
        }

        $pageId = $row['id'];
        $layoutId = $row['layout'];

        $translator = Controller::getContainer()->get('translator');

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
                    'text'  => $translator->trans('setup.global.unlink', [], 'setup'),
                    'class' => 'disconnect',
                    'group' => 'actions'
                ]
            ];

            $optgroups = [
                [
                    'label' => $translator->trans('setup.global.actions', [], 'setup'),
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
                        'label' => $theme->name,
                        'value' => $theme->id
                    ];
                }
            }

            $layoutStructure = $importer->getArchiveContentByFilename(LayoutModel::getTable(), [
                'value' => $row['layout'],
                'field' => 'id'
            ]);

            return [
                $connectionFieldName => [
                    $values ?? [],
                    FormPromptType::SELECT,
                    [
                        'class'         => 'w50',
                        'label'         => $translator->trans('setup.prompt.page.layout.label', [], 'setup'),
                        'description'   => $translator->trans('setup.prompt.page.layout.description', [], 'setup'),
                        'explanation'   => [
                            'type'        => 'TABLE',
                            'description' => $translator->trans('setup.prompt.page.layout.explanation', [], 'setup'),
                            'content'     => $layoutStructure ?? []
                        ],
                        'optgroupField' => 'group',
                        'optgroups'     => $optgroups ?? []
                    ]
                ]
            ];
        }

        return null;
    }

    /**
     * Handles the relationship between a page and its layout after layouts are imported (set by self::setLayoutConnection).
     *
     * @category AFTER_IMPORT
     *
     * @param array<PageModel, array> $collection
     */
    static function connectLayout(array $collection, TableImport $importer): void
    {
        /** @var LayoutModel $model*/
        [$model, $row] = $collection;

        // Skip all layouts that not includes in the _connectLayout-connection
        if(!$pageId = $importer->getConnection($row['id'], '_connectLayout'))
        {
            return;
        }

        // Get new page id by page connections
        if(!$pageId = $importer->getConnection($pageId, PageModel::getTable()))
        {
            return;
        }

        // Get page to set the layout connection afterward
        if($page = PageModel::findByPk($pageId))
        {
            // Overwrite page-layout with the new id of the layout
            $page->layout = $model->id;
            $page->save();
        }
    }

    /**
     * Handles the relationship with the field jumpTo / twoFactorJumpTo and connected pages.
     *
     * @category AFTER_IMPORT
     *
     * @param array<PageModel, array> $collection
     */
    static function setPageJumpToConnection(array $collection, TableImport $importer): void
    {
        /** @var PageModel $model*/
        [$model, $row] = $collection;

        switch ($row['type'])
        {
            case 'root':
            case 'rootfallback':
                if($row['enforceTwoFactor'])
                {
                    if($connectedId = $importer->getConnection($row['twoFactorJumpTo']))
                    {
                        $model->twoFactorJumpTo = $connectedId;
                        $model->save();
                    }
                }

                break;

            case 'error_401':
            case 'error_403':
            case 'error_404':
            case 'error_503':
                if(!$row['autoforward'])
                {
                    return;
                }

            case 'redirect':
                // ToDo: Handle insert tags
            case 'forward':
                if($row['jumpTo'])
                {
                    if($connectedId = $importer->getConnection($row['jumpTo']))
                    {
                        $model->jumpTo = $connectedId;
                        $model->save();
                    }
                }
        }
    }
}
