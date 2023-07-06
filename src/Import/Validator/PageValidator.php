<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
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

    public static function getTrigger(): string
    {
        return PageModel::getTable();
    }

    public static function getModel(): string
    {
        return PageModel::class;
    }

    /**
     * Handles the selection of a page root.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function selectRootPage(array &$row, TableImport $importer): ?array
    {
        // Skip the validator if it is not a root page or no pages exists
        if($row['type'] !== 'root' || PageModel::countAll() === 0)
        {
            return null;
        }

        $fieldName = 'rootPage_' . $row['id'];

        if(null === ($rootPage = $importer->getPromptValue($fieldName)))
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
                $fieldName => [
                    $values,
                    FormPromptType::SELECT,
                    [
                        'label'             => $translator->trans('setup.prompt.page.rootPage.label', ['%pageTitle%' => $row['title']], 'setup'),
                        'description'       => $translator->trans('setup.prompt.page.rootPage.description', [], 'setup'),
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

            /*if($rootPage !== '0')
            {
                // Overriding the page root check during import
                $row['_keep'] = true;

                // Add id connection for child tables
                $importer->addConnection(0, $rootPage);
            }*/
        }

        return null;
    }

    /**
     * Handles the relationship between a page and its layout.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setLayoutConnection(array &$row, TableImport $importer): ?array
    {
        return self::setPageLayoutConnection('layout', $row, $importer);
    }

    /**
     * Handles the relationship between a page and its subpage layout.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setSubpageLayoutConnection(array &$row, TableImport $importer): ?array
    {
        return self::setPageLayoutConnection('subpageLayout', $row, $importer);
    }

    /**
     * Handles the relationship between a page and its layout after layouts are imported (set by self::setLayoutConnection).
     *
     * @category AFTER_IMPORT
     *
     * @param array<array<LayoutModel, array>> $importCollection
     */
    public static function connectPageLayouts(array $importCollection, TableImport $importer): void
    {
        if(!($connections = $importer->getConnection(null, '_connectPageLayout')) || !count($importCollection))
        {
            return;
        }

        // Get old layout ids by the collection values and filter duplicates
        $layoutsToBeConnected = array_filter(array_values($connections));

        // Create new var to collect connected layout ids (oldId -> newId)
        $layoutIds = null;

        // Determine the new layout IDs
        foreach ($layoutsToBeConnected as $layoutId)
        {
            if($connectedId = $importer->getConnection($layoutId, LayoutModel::getTable()))
            {
                $layoutIds[$layoutId] = $connectedId;
            }
        }

        // Loop through the connections to determine the pageId and field
        foreach (array_keys($connections) as $idField)
        {
            [$id, $field] = explode('_', $idField);

            // Get connected page id
            if(!$pageId = $importer->getConnection($id, PageModel::getTable()))
            {
                continue;
            }

            // Get page model by the new id
            if(!$page = PageModel::findById($pageId))
            {
                continue;
            }

            // Check if the layout id is set and connect the id with the page-layout
            if(!$layoutId = ($layoutIds[$page->$field] ?? null))
            {
                continue;
            }

            $page->$field = $layoutId;
            $page->save();
        }
    }

    /**
     * Handles the relationship with the field jumpTo / twoFactorJumpTo and connected pages.
     *
     * @category AFTER_IMPORT_ROW
     *
     * @param array<PageModel, array> $collection
     */
    public static function setPageJumpToConnection(array $collection, TableImport $importer): void
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
