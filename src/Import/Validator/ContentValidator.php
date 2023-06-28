<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\FormModel;
use Contao\ModuleModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;

/**
 * Validator class for validating the content records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
abstract class ContentValidator implements ValidatorInterface
{
    use ValidatorTrait;

    public static function getTrigger(): string
    {
        return ContentModel::getTable();
    }

    public static function getModel(): string
    {
        return ContentModel::class;
    }

    /**
     * Handles the relationship between content elements and its references except the relationship between content
     * elements among themselves.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setIncludes(array &$row, AbstractPromptImport $importer): ?array
    {
        switch($row['type'])
        {
            case 'article':
                $connectionField = 'articleAlias';
                $connectionModel = ArticleModel::class;
                break;

            case 'form':
                $connectionField = 'form';
                $connectionModel = FormModel::class;
                break;

            case 'module':
                $connectionField = 'module';
                $connectionModel = ModuleModel::class;
                break;

            case 'teaser':
                $connectionField = 'article';
                $connectionModel = ArticleModel::class;
                break;

            default:
                return null;
        }

        $skip     = [];
        $id       = $row['id'];
        $parentId = $row[ $connectionField ];

        $connectionName  = $connectionField . '_connection';
        $connectionTable = $connectionModel::getTable();
        $fieldName       = $connectionField . '_connection_' . $id;

        $translator = Controller::getContainer()->get('translator');

        // Skip if we find a connection
        if(($connectedId = $importer->getConnection($parentId, $connectionTable)) !== null)
        {
            // Set connection
            $row[ $connectionField ] = $connectedId;

            return null;
        }

        // Check if we got a prompt response and should skip prompts of the same ID
        if($importer->getFlashConnection($parentId, $connectionName))
        {
            $skip[] = $parentId;
        }

        // Check if we have already received a user decision
        if($connectedId = (int) $importer->getPromptValue($fieldName))
        {
            // Set connection
            $row[ $connectionField ] = $connectedId;

            // Add id connection for child row
            $importer->addConnection($parentId, $connectedId, $connectionTable);
        }
        else
        {
            if(\in_array($parentId, $skip))
            {
                return null;
            }

            // Add a flash connection to display prompts for the same connections only once
            $importer->addFlashConnection($parentId, $id, $connectionName);

            if($records = $connectionModel::findAll())
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
            $parentStructure = $importer->getArchiveContentByFilename($connectionTable, [
                'value' => $parentId,
                'field' => 'id'
            ]);

            return [
                $fieldName => [
                    $values ?? [],
                    FormPromptType::SELECT,
                    [
                        'class'       => 'w50',
                        'label'       => $translator->trans('setup.prompt.content.content.includes.' . $connectionField . '.title', [], 'setup'),
                        'description' => $translator->trans('setup.prompt.content.content.includes.' . $connectionField . '.description', [], 'setup'),
                        'explanation' => [
                            'type'        => 'TABLE',
                            'description' => $translator->trans('setup.prompt.content.content.includes.' . $connectionField . '.explanation', [], 'setup'),
                            'content'     => $parentStructure ?? []
                        ]
                    ]
                ]
            ];
        }


        return null;
    }

    /**
     * Handles single files (singleSRC, posterSRC) in content elements and cleans up fields which should not be set.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setFileConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $connectionField = null;

        // Method to clean up the rows to the essential fields
        $cleaner = static function(?array $keepFields = null) use (&$row): void
        {
            $fields = ['singleSRC', 'posterSRC'];

            // Remove all fields from row
            if(null === $keepFields)
            {
                foreach ($fields as $field)
                {
                    $row[$field] = null;
                }
            }else{
                foreach (array_diff($fields, $keepFields) as $field)
                {
                    $row[$field] = null;
                }
            }
        };

        switch($row['type'])
        {
            case 'accordionSingle':
            case 'text':
                if(!$row['addImage'])
                {
                    $cleaner();
                    return null;
                }else{
                    $cleaner(['singleSRC']);
                }

                $connectionField = 'singleSRC';
                break;

            case 'hyperlink':
                if(!$row['useImage'])
                {
                    $cleaner();
                    return null;
                }else{
                    $cleaner(['singleSRC']);
                }

                $connectionField = 'singleSRC';
                break;

            case 'youtube':
            case 'vimeo':
                if(!$row['splashImage'])
                {
                    $cleaner();
                    return null;
                }else{
                    $cleaner(['singleSRC']);
                }

                $connectionField = 'singleSRC';
                break;

            case 'player':
                $cleaner(['posterSRC']);
                $connectionField = 'posterSRC';
                break;

            case 'image':
            case 'download':
                $cleaner(['singleSRC']);
                $connectionField = 'singleSRC';
                break;

            // Sometimes it happens that content element types have changed and thus a UUID still exists within the
            // possible fields. In this case, these fields must be cleaned up.
            default:
                $cleaner();
        }

        // Skip if no connection field is set or the field is empty
        if(!$connectionField || !$row[$connectionField])
        {
            return null;
        }

        return self::setSingleFileConnection(self::getModel(), $connectionField, $row, $importer);
    }

    /**
     * Handles the relationship between content elements among themselves.
     *
     * @category AFTER_IMPORT_ROW
     *
     * @param array<ContentModel, array> $collection
     */
    public static function setContentIncludes(array $collection, AbstractPromptImport $importer): void
    {
        /** @var ContentModel $model*/
        [$model, $row] = $collection;

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
