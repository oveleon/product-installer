<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\Controller;
use Contao\FilesModel;
use Contao\MemberGroupModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating the various records during and after import.
 * Unlike other validators, the Collection Validator handles several models at once and thus provides one and the same
 * functionality for all of them.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class CollectionValidator
{
    use ValidatorTrait;

    /**
     * Handles the connection to a redirect page.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setJumpToPageConnection(array &$row, TableImport $importer, string|Model $model): ?array
    {
        switch(true)
        {
            case $model instanceof MemberGroupModel:
                if(!$importer->hasValue($row, 'redirect'))
                {
                    return null;
                }

            default:
                if(!$importer->hasValue($row, 'jumpTo'))
                {
                    return null;
                }
        }

        return self::setFieldPageConnection($model, 'jumpTo', $row, $importer);
    }

    /**
     * Handles the connection of insert tags withing selected elements and modules including custom elements.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setInsertTagConnections(array &$row, TableImport $importer, string|Model $model): ?array
    {
        switch ($row['type'])
        {
            // Skip types where we know they will never have insert tags to save performance
            case 'accordionStop':
            case 'sliderStop':
            case 'article':
            case 'alias':
            case 'module':
            case 'teaser':
            case 'copyArticle':
            case 'copyElement':
            case 'wrapperStopContent':
            case 'wrapperStop':
            case 'tabStop':
                return null;
        }

        // Variable to remember if it is a custom element
        $isCustomElement = false;

        // Check for custom elements and use data instead of row
        if(str_starts_with($row['type'], 'rsce_'))
        {
            if(null === $row['rsce_data'])
            {
                return null;
            }

            // Convert rsce data to array
            $content = json_decode($row['rsce_data'], true);

            // Set custom element flag
            $isCustomElement = true;
        }
        // Otherwise use row
        else $content = $row;

        // Variable to intercept non-connectable connections
        $notConnectable = null;

        // Detect / replace insert tags
        $content = self::detectInsertTagsAndReplace($content, $notConnectable, $row, $model, $importer);

        // Check for non-connectable files and create prompt fields
        if(null !== $notConnectable)
        {
            $translator = Controller::getContainer()->get('translator');
            $fields = null;

            foreach ($notConnectable as $missingConnection)
            {
                // Create unique field name
                $fieldName = 'custom_' . $missingConnection['table'] . '_' . $missingConnection['value'];

                // Create prompt fields once for each connection
                if(!\array_key_exists($fieldName, $fields ?? []))
                {
                    // Detect type of fields and create field array
                    switch ($missingConnection['table'])
                    {
                        case ArticleModel::getTable():
                        case PageModel::getTable():

                            // Check for a prompt response and make the connections when set
                            if($connectedId = $importer->getPromptValue($fieldName))
                            {
                                // Save as new connection
                                $importer->addConnection($missingConnection['value'], $connectedId, $missingConnection['table']);
                            }
                            else
                            {
                                $values = [];

                                // Get selectable values by table
                                if($missingConnection['table'] === PageModel::getTable())
                                {
                                    // Get page structure
                                    $values = System::getContainer()
                                        ->get("Oveleon\ProductInstaller\Util\PageUtil")
                                        ->setPages()
                                        ->getPagesSelectable(true);
                                }
                                elseif($missingConnection['table'] === ArticleModel::getTable())
                                {
                                    // Get page structure
                                    $values = System::getContainer()
                                        ->get("Oveleon\ProductInstaller\Util\PageUtil")
                                        ->setPages()
                                        ->setArticles()
                                        ->getArticleSelectable(true);
                                }

                                // Set field
                                $fields[$fieldName] = [
                                    $values,
                                    FormPromptType::SELECT,
                                    [
                                        'label'         => $translator->trans('setup.prompt.collection.custom_page.label', ['%title%' => $row['type']], 'setup'),
                                        'description'   => $translator->trans('setup.prompt.collection.custom_page.description', [], 'setup'),
                                        'class'         => 'pages',
                                    ]
                                ];
                            }

                            break;
                        // ToDo: Add fields for other cases
                    }
                }
            }

            if(null === $fields)
            {
                // Validate connections again
                $content = self::detectInsertTagsAndReplace($content, $notConnectable, $row, $model, $importer);
            }
            else
            {
                return $fields;
            }
        }

        // Overwrite with new data
        if($isCustomElement)
            $row['rsce_data'] = json_encode($content);
        else
            $row = $content;

        return null;
    }

    /**
     * Handles the relationship between an insert tag and its connected field after connected table are imported (set
     * by self::setInsertTagConnections).
     *
     * @category AFTER_IMPORT
     *
     * @param array<array<Model, array>> $importCollection
     */
    public static function connectInsertTag(array $importCollection, TableImport $importer): void
    {
        foreach ($importCollection as $collection)
        {
            [$model, $row] = $collection;

            // Skip all that not includes in the _connectInsertTag-connection
            if(!$updateInfo = $importer->getConnection($row['id'], '_connectInsertTag'))
            {
                continue;
            }

            // Remove the connection to validate only once
            $importer->removeConnection($row['id'], '_connectInsertTag');

            // Decode the connection information
            $updateInfo = json_decode($updateInfo, true);

            // The ID to be updated
            $idBeforeImport = $updateInfo['id'];

            // The model class to be updated
            /** @var Model|string $modelClass */
            $modelClass = $updateInfo['modelClass'];

            // Try to get the new id
            if(!$idAfterImport = $importer->getConnection($idBeforeImport, $modelClass::getTable()))
            {
                continue;
            }

            // Try to get the new imported model
            /** @var Model $updateModel */
            if(!$updateModel = $modelClass::findById($idAfterImport))
            {
                continue;
            }

            // Get model row, update insert tags and save
            $row = $updateModel->row();

            // Collect not connectable fields
            $notConnectable = null;

            // Collect the modified fields
            $modifiedFields = null;

            // Detect / replace insert tags
            $row = self::detectInsertTagsAndReplace($row, $notConnectable, $row, $modelClass, $importer, $modifiedFields);

            // Update model
            $updateModel->setRow($row);

            // Mark fields as modified
            foreach ($modifiedFields ?? [] as $modifiedField)
            {
                $updateModel->markModified($modifiedField);
            }

            // Save model
            $updateModel->save();
        }
    }

    /**
     * Handles the connection of files withing custom content elements and modules.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setCustomElementSingleFileConnections(array &$row, TableImport $importer): ?array
    {
        if(!str_starts_with($row['type'], 'rsce_') || null === $row['rsce_data'])
        {
            return null;
        }

        // Variable to intercept non-connectable file connections
        $notConnectable = null;

        // Convert rsce data to array
        $content = json_decode($row['rsce_data'], true);

        // Helper method to overwrite connections and collecting non-connectable file connections
        $fnConnectFiles = static function ($subset) use (&$fnConnectFiles, &$notConnectable, $importer)
        {
            foreach ($subset as $key => $value)
            {
                if(\is_array($value))
                {
                    $subset[$key] = $fnConnectFiles($value);
                }
                elseif($key === 'singleSRC')
                {
                    if($connectedUuid = $importer->getConnection($value, FilesModel::getTable()))
                    {
                        $subset[$key] = $connectedUuid;
                    }
                    else
                    {
                        $notConnectable[] = $value;
                    }
                }
            }

            return $subset;
        };

        // Validate file connections
        $content = $fnConnectFiles($content);

        // Check for non-connectable files and create prompt fields
        if(null !== $notConnectable)
        {
            $translator = Controller::getContainer()->get('translator');
            $fields = null;

            // Filter duplicates and loop through array
            foreach (array_filter($notConnectable) as $uuid)
            {
                $fieldName = 'rsce_file_' . $uuid;

                // Check for a prompt response and make the connections when set.
                if($connectedFile = $importer->getPromptValue($fieldName))
                {
                    if($file = FilesModel::findByPath($connectedFile))
                    {
                        // Convert binary to string
                        $connectedId = StringUtil::binToUuid($file->uuid);

                        // Save as new connection
                        $importer->addConnection($uuid, $connectedId, FilesModel::getTable());
                    }
                }
                // Otherwise create prompt fields
                else
                {
                    // Try to resolve and display the non-imported file.
                    if($fileStructure = $importer->getArchiveContentByFilename(FilesModel::getTable()))
                    {
                        $fileRows = \array_filter($fileStructure, function ($item) use ($uuid) {
                            return $uuid === $item['uuid'];
                        });

                        $images = '';

                        foreach ($fileRows ?? [] as $fileRow)
                        {
                            // Detect known mime types
                            switch (strtolower($fileRow['extension']))
                            {
                                case 'svg':
                                    $mime = 'image/svg+xml';
                                    break;

                                case 'jpg':
                                    $mime = 'image/jpeg';
                                    break;

                                default:
                                    $mime = 'image/' . $fileRow['extension'];
                            }

                            $imageContent  = $importer->getArchiveContentByFilename($fileRow['path'], null, false, false);
                            $imageBase64   = 'data:'. $mime . ';base64,' . base64_encode($imageContent);
                            $images       .= sprintf('<img src="%s" alt="original"/>', $imageBase64);
                        }
                    }

                    $fields[$fieldName] = [
                        [],
                        FormPromptType::FILE,
                        [
                            'label'       => $translator->trans('setup.prompt.collection.custom_file.label', ['%title%' => $row['type']], 'setup'),
                            'description' => $translator->trans('setup.prompt.collection.custom_file.description', [], 'setup'),
                            'explanation' => [
                                'type'        => 'HTML',
                                'description' => $translator->trans('setup.prompt.collection.custom_file.explanation', [], 'setup'),
                                'content'     => $images ?? ''
                            ]
                        ]
                    ];
                }
            }

            if(null === $fields)
            {
                // Validate file connections again
                $content = $fnConnectFiles($content);
            }
            else
            {
                return $fields;
            }
        }

        // Encode data and overwrite field
        $row['rsce_data'] = json_encode($content);

        return null;
    }
}
