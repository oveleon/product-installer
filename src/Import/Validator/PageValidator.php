<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
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
     */
    static public function selectRootPage(array &$row, AbstractPromptImport $importer): ?array
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

            if($pages = PageModel::findAll(['order' => 'id ASC, sorting ASC']))
            {
                /** @var PageUtil $pageUtil */
                $pageUtil = System::getContainer()
                                ->get("Oveleon\ProductInstaller\Util\PageUtil")
                                ->setPages($pages);

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
            if($rootPage !== '0')
            {
                $row['_skip'] = true;

                // Add id connection for child tables
                $importer->addConnection($row['id'], $rootPage);
            }
        }

        return null;
    }

    /**
     * Treats the relationship between a page and its layout.
     */
    static public function setLayoutConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        // Skip if the page has no own layout connection
        if(!$row['includeLayout'])
        {
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
     * Treats the relationship with the field jumpTo / twoFactorJumpTo and connected pages.
     */
    static function setPageJumpToConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        switch ($row['type'])
        {
            case 'root':
            case 'rootfallback':
                if($row['enforceTwoFactor'])
                {
                    return self::setFieldPageConnection(self::getModel(), 'twoFactorJumpTo', $row, $importer);
                }

                return null;

            case 'error_401':
            case 'error_403':
            case 'error_404':
            case 'error_503':
                if(!$row['autoforward'])
                {
                    return null;
                }

                break;

            default:
                if(!$row['jumpTo'])
                {
                    return null;
                }
        }

        return self::setFieldPageConnection(self::getModel(), 'jumpTo', $row, $importer);
    }
}
