<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FilesModel;
use Contao\MemberGroupModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;
use Oveleon\ProductInstaller\InsertTag;
use Oveleon\ProductInstaller\Util\InsertTagUtil;

/**
 * Validator class for validating the various records during and after import.
 * Unlike other validators, the Collection Validator handles several models at once and thus provides one and the same functionality for all of them.
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
    static function setJumpToPageConnection(array &$row, AbstractPromptImport $importer, string|Model $model): ?array
    {
        switch(true)
        {
            case $model instanceof MemberGroupModel:
                if(!$row['redirect'])
                {
                    return null;
                }

            default:
                if(!$row['jumpTo'])
                {
                    return null;
                }
        }

        return self::setFieldPageConnection($model, 'jumpTo', $row, $importer);
    }

    /**
     * Handles the connection of insert tags withing custom content elements and modules.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setCustomElementInsertTagConnections(array &$row, TableImport $importer): ?array
    {
        if(!str_starts_with($row['type'], 'rsce_') || null === $row['rsce_data'])
        {
            return null;
        }

        // Variable to intercept non-connectable connections
        $notConnectable = null;

        /** @var InsertTagUtil $insertTagUtil */
        $insertTagUtil = System::getContainer()->get('Oveleon\ProductInstaller\Util\InsertTagUtil');

        // Convert rsce data to array
        $content = json_decode($row['rsce_data'], true);

        // Helper method to replace insert tags
        $fnReplaceInsertTags = static function (string $string, array $insertTags) use (&$fnReplaceInsertTags, &$notConnectable, $importer)
        {
            foreach ($insertTags as $insertTag)
            {
                /** @var InsertTag $insertTag */
                $value = $insertTag->getValue();

                // Check for nested insert tags
                if($value instanceof InsertTag)
                {
                    return $fnReplaceInsertTags($string, [$insertTag->getValue()]);
                }

                $search  = $insertTag->toString();
                $replace = null;
                $table   = $insertTag->getRelatedTable();

                switch ($table)
                {
                    case PageModel::getTable():
                        // Check linked pages
                        if($insertTag->getCommand(true) === 'link')
                        {
                            // Check existing connections
                            if($connectedId = $importer->getConnection($value, $table))
                            {
                                // Overwrite the insert tag value with the connected id
                                $insertTag->setValue($connectedId);

                                // Set replace with the new insert tag string
                                $replace = $insertTag->toString();
                            }
                            // Set non-connectable id
                            else
                            {
                                $notConnectable[] = [
                                    'table' => $table,
                                    'value' => $value
                                ];
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
        };

        // Helper method to detect insert tags recursive
        $fnDetectInsertTags = static function (array $subset) use (&$fnDetectInsertTags, $fnReplaceInsertTags, $insertTagUtil, $importer)
        {
            foreach ($subset as $key => $value)
            {
                if(\is_array($value))
                {
                    $subset[$key] = $fnDetectInsertTags($value);
                }
                elseif($insertTagUtil->hasInsertTags($value))
                {
                    if($insertTags = $insertTagUtil->extractInsertTags($value))
                    {
                        $subset[$key] = $fnReplaceInsertTags($value, $insertTags);
                    }
                }
            }

            return $subset;
        };

        // detect / replace insert tags
        $content = $fnDetectInsertTags($content);

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
                        case PageModel::getTable():

                            // Check for a prompt response and make the connections when set
                            if($connectedId = $importer->getPromptValue($fieldName))
                            {
                                // Save as new connection
                                $importer->addConnection($missingConnection['value'], $connectedId, $missingConnection['table']);
                            }
                            else
                            {
                                // Get page structure
                                $values = System::getContainer()
                                    ->get("Oveleon\ProductInstaller\Util\PageUtil")
                                    ->setPages()
                                    ->getPagesSelectable(true);

                                // Set field
                                $fields[$fieldName] = [
                                    $values,
                                    FormPromptType::SELECT,
                                    [
                                        'label'         => $translator->trans('setup.prompt.collection.custom_page.label', ['%pageTitle%' => $row['type']], 'setup'),
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
                $content = $fnDetectInsertTags($content);
            }
            else
            {
                return $fields;
            }
        }

        // Overwrite with new data
        $row['rsce_data'] = serialize($content);

        return null;
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
