<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\FilesModel;
use Contao\FormModel;
use Contao\ModuleModel;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;

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
    public static function setIncludes(array &$row, TableImport $importer): ?array
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
        // Create prompt fields
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
                        'info'  => $record->id,
                        'class' => $connectionField
                    ];
                }
            }

            // Try to get missing record information
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
    public static function setSingleFileConnection(array &$row, TableImport $importer): ?array
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

        // Get translator
        $translator = Controller::getContainer()->get('translator');

        return $importer->useIdentifierConnectionLogic($row, $connectionField, ContentModel::getTable(), FilesModel::getTable(), [
            'class'       => 'w50',
            'isFile'      => true,
            'widget'      => FormPromptType::FILE,
            'popupTitle'  => $translator->trans('setup.prompt.content.' . $connectionField . '.title', [], 'setup'),
            'label'       => $translator->trans('setup.prompt.content.' . $connectionField . '.title', [], 'setup'),
            'description' => $translator->trans('setup.prompt.content.' . $connectionField . '.description', [], 'setup'),
            'explanation' => self::getFileExplanationClosure(
                $row,
                $connectionField,
                $importer,
                $translator->trans('setup.prompt.content.singleSRC.explanation', [], 'setup')
            )
        ]);
    }

    /**
     * Handles single files (playerSRC) in content elements.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setPlayerConnection(array &$row, TableImport $importer): ?array
    {
        if($row['type'] !== 'player' || !$importer->hasValue($row, 'playerSRC'))
        {
            return null;
        }

        // Get translator
        $translator = Controller::getContainer()->get('translator');

        return $importer->useIdentifierConnectionLogic($row, 'playerSRC', ContentModel::getTable(), FilesModel::getTable(), [
            'class'       => 'w50',
            'isFile'      => true,
            'widget'      => FormPromptType::FILE,
            'multiple'    => true,
            'popupTitle'  => $translator->trans('setup.prompt.content.playerSRC.title', [], 'setup'),
            'label'       => $translator->trans('setup.prompt.content.playerSRC.title', [], 'setup'),
            'description' => $translator->trans('setup.prompt.content.playerSRC.description', [], 'setup'),
            'allowedExtensions' => 'mp3,mp4,m4a,m4v,webm,ogg,ogv,wma,wmv,ram,rm,mov'
        ]);
    }

    /**
     * Handles multiple files (multiSRC) in content elements.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setMultiFileConnection(array &$row, TableImport $importer): ?array
    {
        switch ($row['type'])
        {
            case 'downloads':
            case 'randomImage':
            case 'gallery':
                if($row['multiSRC'])
                    break;
            default:
                return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'             => $translator->trans('setup.prompt.content.multiSRC.label', [], 'setup'),
            'description'       => $translator->trans('setup.prompt.content.multiSRC.description', [], 'setup'),
            'multiple'          => true,
            'isFile'            => true,
            'widget'            => FormPromptType::FILE,
            'allowedExtensions' => 'css,scss,less'
        ];

        return $importer->useIdentifierConnectionLogic($row, 'multiSRC', ContentModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }

    /**
     * Handles the relationship between content elements among themselves.
     *
     * @category AFTER_IMPORT_ROW
     *
     * @param array<ContentModel, array> $collection
     */
    public static function setContentIncludes(array $collection, TableImport $importer): void
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
