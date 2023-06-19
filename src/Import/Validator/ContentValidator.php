<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\FilesModel;
use Contao\FormModel;
use Contao\ModuleModel;
use Contao\StringUtil;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;

/**
 * Validator class for validating the content records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class ContentValidator implements ValidatorInterface
{
    /**
     * Treats the relationship between content elements and its references except the
     * relationship between content elements among themselves.
     */
    static function setIncludes(array &$row, AbstractPromptImport $importer): ?array
    {
        switch($row['type'])
        {
            case 'article':
                $connectorField = 'articleAlias';
                $connectorModel = ArticleModel::class;
                break;

            case 'form':
                $connectorField = 'form';
                $connectorModel = FormModel::class;
                break;

            case 'module':
                $connectorField = 'module';
                $connectorModel = ModuleModel::class;
                break;

            case 'teaser':
                $connectorField = 'article';
                $connectorModel = ArticleModel::class;
                break;

            default:
                return null;
        }

        $skip = [];
        $id = $row['id'];
        $parentId = $row[ $connectorField ];

        $connectorName = $connectorField . '_connection';
        $connectorTable = $connectorModel::getTable();
        $fieldName  = $connectorField . '_connection_' . $id;

        $translator = Controller::getContainer()->get('translator');

        // Skip if we find a connection
        if(($connectedId = $importer->getConnection($parentId, $connectorTable)) !== null)
        {
            // Set connection
            $row[ $connectorField ] = $connectedId;

            return null;
        }

        // Check if we got a prompt response and should skip prompts of the same ID
        if($importer->getFlashConnection($parentId, $connectorName))
        {
            $skip[] = $parentId;
        }

        // Check if we have already received a user decision
        if($connectedId = (int) $importer->getPromptValue($fieldName))
        {
            // Set connection
            $row[ $connectorField ] = $connectedId;

            // Add id connection for child row
            $importer->addConnection($parentId, $connectedId, $connectorTable);
        }
        else
        {
            if(\in_array($parentId, $skip))
            {
                return null;
            }

            // Add a flash connection to display prompts for the same connections only once
            $importer->addFlashConnection($parentId, $id, $connectorName);

            if($records = $connectorModel::findAll())
            {
                foreach ($records as $record)
                {
                    if($record?->headline && (@unserialize($record->headline) !== false))
                    {
                        // If headline is still empty, show placeholder
                        if(!$record->headline = unserialize($record->headline)['value'])
                        {
                            $record->headline = $translator->trans('setup.placeholder.contentElement', ['%id%' => $record->id], 'setup');
                        }
                    }

                    $values[] = [
                        'value' => $record->id,
                        'text'  => $record?->name ?: ($record?->title ?: $record->headline),
                        'info'  => $record->id
                    ];
                }
            }

            // Try to get missing record
            $parentStructure = $importer->getArchiveContentByFilename($connectorTable, [
                'value' => $parentId,
                'field' => 'id'
            ]);

            return [
                $fieldName => [
                    $values ?? [],
                    FormPromptType::SELECT,
                    [
                        'class'       => 'w50',
                        'label'       => $translator->trans('setup.prompt.content.content.includes.' . $connectorField . '.title', [], 'setup'),
                        'description' => $translator->trans('setup.prompt.content.content.includes.' . $connectorField . '.description', [], 'setup'),
                        'explanation' => [
                            'type'        => 'TABLE',
                            'description' => $translator->trans('setup.prompt.content.content.includes.' . $connectorField . '.explanation', [], 'setup'),
                            'content'     => $parentStructure ?? []
                        ]
                    ]
                ]
            ];
        }


        return null;
    }
    /**
     * Treats with images (UUIDs) in content elements.
     */
    static function setImageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if($row['singleSRC'])
        {
            $source     = StringUtil::binToUuid($row['singleSRC']);
            $connection = 'singleSRC_connection';
            $fieldName  = $connection . '_' . $row['id'];

            // Check if we got a prompt response and should skip prompts of the same ID
            if($importer->getFlashConnection($source, $connection))
            {
                return null;
            }

            if(
                ($connectedUuid = $importer->getConnection($source, FilesModel::getTable())) ||
                ($connectedFile = $importer->getPromptValue($fieldName))
            )
            {
                // get uuid by file
                if($connectedFile ?? null)
                {
                    if($file = FilesModel::findByPath($connectedFile))
                    {
                        $connectedUuid = $file->uuid;
                    }
                }
                else
                {
                    $connectedUuid = StringUtil::uuidToBin($connectedUuid);
                }

                // Overwrite source
                $row['singleSRC'] = $connectedUuid;

                // Set connection
                $importer->addConnection($source, StringUtil::binToUuid($connectedUuid), FilesModel::getTable());
            }
            else
            {
                // Add a flash connection to display prompts for the same connections only once
                $importer->addFlashConnection($source, 1, $connection);

                $translator = Controller::getContainer()->get('translator');

                return [
                    $fieldName => [
                        $values ?? [],
                        FormPromptType::FILE,
                        [
                            'class'       => 'w50',
                            'popupTitle'  => $translator->trans('setup.prompt.content.singleSRC.title', [], 'setup'),
                            'label'       => $translator->trans('setup.prompt.content.singleSRC.title', [], 'setup'),
                            'description' => $translator->trans('setup.prompt.content.singleSRC.description', [], 'setup'),
                            'explanation' => [
                                'type'        => 'TABLE',
                                'description' => $translator->trans('setup.prompt.content.singleSRC.explanation', [], 'setup'),
                                'content'     => $parentStructure ?? []
                            ]
                        ]
                    ]
                ];
            }
        }

        return null;
    }

    /**
     * Treats the relationship between content elements among themselves.
     */
    static function setContentIncludes(ContentModel $model, AbstractPromptImport $importer): void
    {
        if($model->type !== 'alias')
        {
            return;
        }

        if(($connectedId = $importer->getConnection($model->cteAlias, $importer->getTable())) !== null)
        {
            // Set connection and save model
            $model->cteAlias = $connectedId;
            $model->save();
        }
    }


}
