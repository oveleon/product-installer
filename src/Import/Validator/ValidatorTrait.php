<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\Controller;
use Contao\FilesModel;
use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;
use Oveleon\ProductInstaller\InsertTag;
use Oveleon\ProductInstaller\Util\InsertTagUtil;
use Oveleon\ProductInstaller\Util\PageUtil;

/**
 * Validator trait for recurring actions (reusable boilerplate methods).
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
trait ValidatorTrait
{
    /**
     * Connects the specified page field to a new layout.
     */
    public static function setPageLayoutConnection(string $field, array &$row, TableImport $importer): ?array
    {
        // Skip if the page has no own layout connection
        if(!$row['includeLayout'] || ($field === 'subpageLayout' && !$row['subpageLayout']))
        {
            return null;
        }

        // Provided that the layouts have not yet been imported, but are still being imported, we can carry out the connection later.
        if($importer->willBeImported(LayoutModel::getTable()))
        {
            // Add the pageId to validate it again after the layouts have been imported.
            $importer->addConnection($row['id'] . '_' . $field, $row[$field], '_connectPageLayout');

            // Add persist layout validator
            $importer->addLifecycleValidator('connectPageLayouts', LayoutModel::getTable(), [PageValidator::class, 'connectPageLayouts'], ValidatorMode::AFTER_IMPORT);

            return null;
        }

        $pageId = $row['id'];
        $layoutId = $row[$field];

        $translator = Controller::getContainer()->get('translator');

        // Skip if we find a connection
        if(($connectedId = $importer->getConnection($layoutId, LayoutModel::getTable())) !== null)
        {
            // Disconnect layout
            if((int) $connectedId === 0)
            {
                $row['includeLayout'] = 0;
                $row[$field] = 0;
            }
            // Set new layout id to the page
            else
            {
                $row[$field] = $connectedId;
            }

            return null;
        }

        $skip = [];
        $connectionFieldName = 'page' . ucfirst($field) . '_' . $pageId;

        // Check if we got a prompt response and should skip layout prompts of the same ID
        if($importer->getFlashConnection($layoutId, $field . '_page_connection'))
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
                $row[$field] = 0;
            }
            // Set new layout id to the page
            else
            {
                $row[$field] = $connectedId;
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

            $importer->addFlashConnection($layoutId, $pageId, $field . '_page_connection');

            $values = [];

            // Create unlink-action for the field layout
            if($field === 'layout')
            {
                $values = [
                    [
                        'value' => 0,
                        'text'  => $translator->trans('setup.global.unlink', [], 'setup'),
                        'class' => 'disconnect',
                        'group' => 'actions'
                    ]
                ];
            }

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
                'value' => $row[$field],
                'field' => 'id'
            ]);

            return [
                $connectionFieldName => [
                    $values ?? [],
                    FormPromptType::SELECT,
                    [
                        'class'         => 'w50',
                        'label'         => $translator->trans('setup.prompt.page.'.$field.'.label', [], 'setup'),
                        'description'   => $translator->trans('setup.prompt.page.'.$field.'.description', [], 'setup'),
                        'explanation'   => [
                            'type'        => 'TABLE',
                            'description' => $translator->trans('setup.prompt.page.'.$field.'.explanation', [], 'setup'),
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
     * Connects the specified field of the passed source models to a new page.
     */
    public static function setFieldPageConnection(string|Model $sourceModel, string $field, array &$row, TableImport $importer, ?array $extendPromptOptions = null): ?array
    {
        $translator = System::getContainer()->get('translator');

        /** @var PageUtil $pageUtil */
        $values = System::getContainer()
            ->get("Oveleon\ProductInstaller\Util\PageUtil")
            ->setPages()
            ->getPagesSelectable(true);

        // Fetch missing structure from the archive to give the user an overview of which page from his own structure would fit
        // Try to deserialize the field value for multiple pages (e.g. field `pages`)
        $missingStructure = $importer->getArchiveContentByFilename(PageModel::getTable(), [
            'value' => StringUtil::deserialize($row[$field]),
            'field' => 'id',
            'keys'  => [
                'id',
                'title',
                'pageTitle',
                'type',
                'alias',
                'layout'
            ]
        ]);

        // Extend prompt options
        $translatorNamePart = str_replace("tl_", "", $sourceModel::getTable());
        $promptOptions = [
            'class'         => 'w50',
            'label'         => $translator->trans('setup.prompt.'.$translatorNamePart.'.'.$field.'.label', [], 'setup'),
            'description'   => $translator->trans('setup.prompt.'.$translatorNamePart.'.'.$field.'.description', [], 'setup')
        ];

        $explanationField = 'setup.prompt.'.$translatorNamePart.'.'.$field.'.explanation';
        $explanationText  = $translator->trans($explanationField, [], 'setup');
        $hasExplanation   = $explanationText !== $explanationField;

        if($hasExplanation)
        {
            $promptOptions['explanation'] = [
                'type'        => 'HTML',
                'description' => $explanationText,
            ];

            if(!empty($missingStructure))
            {
                $promptOptions['explanation']['type'] = 'TABLE';
                $promptOptions['explanation']['content'] = $missingStructure;
            }
        }

        if(null !== $extendPromptOptions)
        {
            $promptOptions = $promptOptions + $extendPromptOptions;
        }

        return $importer->useIdentifierConnectionLogic($row, $field, $sourceModel::getTable(), PageModel::getTable(), $promptOptions, $values ?? []);
    }

    /**
     * Returns a file explanation closure that attempts to preview a non-imported image.
     */
    public static function getFileExplanationClosure(array $row, string $connectionField, TableImport $importer, string $description): \closure
    {
        return static function () use ($row, $connectionField, $importer, $description): ?array {
            // Try to resolve and display the non-imported file.
            if ($fileStructure = $importer->getArchiveContentByFilename(FilesModel::getTable())) {
                $fileRows = \array_filter($fileStructure, function ($item) use ($row, $connectionField) {
                    return $row[$connectionField] === $item['uuid'];
                });

                $images = '';

                foreach ($fileRows ?? [] as $fileRow) {
                    // Detect known mime types
                    switch (strtolower($fileRow['extension'])) {
                        case 'svg':
                            $mime = 'image/svg+xml';
                            break;

                        case 'jpg':
                            $mime = 'image/jpeg';
                            break;

                        default:
                            $mime = 'image/' . $fileRow['extension'];
                    }

                    $imageContent = $importer->getArchiveContentByFilename($fileRow['path'], null, false, false);
                    $imageBase64  = 'data:' . $mime . ';base64,' . base64_encode($imageContent);
                    $images       .= sprintf('<img src="%s" alt="original"/>', $imageBase64);
                }
            }

            return [
                'type'        => 'HTML',
                'description' => $description,
                'content'     => $images ?? ''
            ];
        };
    }

    /**
     * Helper method to detect and replace insert tags, generate prompt or add lifecycle validators if not connectable.
     */
    public static function detectInsertTagsAndReplace(array $subset, ?array &$notConnectable, array $row, string $model, TableImport $importer, ?array &$modifiedFields = null): array
    {
        foreach ($subset as $key => $value)
        {
            if(\is_array($value))
            {
                $subset[$key] = self::detectInsertTagsAndReplace($value, $notConnectable, $row, $model, $importer);
            }
            elseif(InsertTagUtil::hasInsertTags($value))
            {
                if($insertTags = InsertTagUtil::extractInsertTags($value))
                {
                    // Replace insert tag and add prompts to importer dynamically
                    $subset[$key] = self::replaceInsertTagsOrPrompt($value, $insertTags, $notConnectable, $row, $model, $importer);

                    // Add field to modified field collection
                    $modifiedFields[] = $key;
                }
            }
        }

        return $subset;
    }

    /**
     * Helper method to replace insert tags, generate prompt or add lifecycle validators if not connectable.
     */
    public static function replaceInsertTagsOrPrompt(string $string, array $insertTags, ?array &$notConnectable, array $row, string $model, TableImport $importer): string
    {
        foreach ($insertTags as $insertTag)
        {
            /** @var InsertTag $insertTag */
            $value = $insertTag->getValue();

            // Check for nested insert tags
            if($value instanceof InsertTag)
            {
                return self::replaceInsertTagsOrPrompt($string, [$insertTag->getValue()], $notConnectable, $row, $model, $importer);
            }

            $search  = $insertTag->toString();
            $replace = null;
            $table   = $insertTag->getRelatedTable();

            switch ($table)
            {
                case ArticleModel::getTable():
                case PageModel::getTable():

                    if(
                        ($command = $insertTag->getCommand(true)) === 'link' ||         // 1. Handle pages
                        ($command = $insertTag->getCommand())               === 'insert_article'  // 2. Handle article includes
                    )
                    {
                        // Check existing connections
                        if($connectedId = $importer->getConnection($value, $table))
                        {
                            // Overwrite the insert tag value with the connected id
                            $insertTag->setValue($connectedId);

                            // Set replace with the new insert tag string
                            $replace = $insertTag->toString();
                        }
                        // Set non-connectable or create lifecycle validator
                        else
                        {
                            // If the table is still imported, we will try to connect later
                            if($importer->willBeImported($table))
                            {
                                // Add insert tag connection to retrieve them in the new validator (searchId, pageId). See method `connectInsertTag` for more information.
                                $importer->addConnection($value, json_encode(['id' => $row['id'], 'modelClass' => $model]), '_connectInsertTag');

                                // Add persist layout validator
                                $importer->addLifecycleValidator('connectInsertTag_' . $value, $table, [CollectionValidator::class, 'connectInsertTag'], ValidatorMode::AFTER_IMPORT);
                            }
                            // Otherwise we need a prompt
                            else
                            {
                                $notConnectable[] = [
                                    'table'   => $table,
                                    'value'   => $value,
                                    'command' => $command
                                ];
                            }
                        }
                    }

                    break;

                // ToDo: Add more cases
            }

            if(null !== $replace)
            {
                $string = str_replace($search, $replace, $string);
            }
        }

        return $string;
    }

    /**
     * If certain connections cannot be made, a check is made to see whether the data records to be connected already exist (ctm_id).
     */
    public static function detectExistingRecordId(TableImport $importer, Model|string $model, int $id): ?int
    {
        $t = $model::getTable();

        // Get information from archive file
        $connectedStructure = $importer->getArchiveContentByFilename($t, [
            'value' => $id,
            'field' => 'id'
        ]);

        if(!$connectedStructure || !\array_key_exists('ctm_id', $connectedStructure))
        {
            return null;
        }

        if($record = $model::findOneBy(["$t.ctm_id=?"], [$connectedStructure['ctm_id']]))
        {
            return $record->id;
        }

        return null;
    }
}
