<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FilesModel;
use Contao\MemberGroupModel;
use Contao\Model;
use Contao\StringUtil;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;

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
     * Handles the connection of insert tags withing rsce content elements and modules.
     */
    static function setRsceInsertTagConnections(array &$row, AbstractPromptImport $importer, string|Model $model): ?array
    {
        // ToDo: Handle insert tags within rsce elements

        $test = '';

        // Id 3657

        return null;
    }

    /**
     * Handles the connection of files withing rsce content elements and modules.
     */
    static function setRsceSingleFileConnections(array &$row, TableImport $importer, string|Model $model): ?array
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
                            'label'       => $translator->trans('setup.prompt.collection.rsce_file.label', ['%title%' => $row['type']], 'setup'),
                            'description' => $translator->trans('setup.prompt.collection.rsce_file.description', [], 'setup'),
                            'explanation' => [
                                'type'        => 'HTML',
                                'description' => $translator->trans('setup.prompt.collection.rsce_file.explanation', [], 'setup'),
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
