<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Util\PageUtil;

/**
 * Validator trait for recurring actions.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
trait ValidatorTrait
{
    /**
     * Connects the specified field of the passed source models to a new page.
     */
    public static function setFieldPageConnection(string|Model $sourceModel, string $field, array &$row, AbstractPromptImport $importer, ?array $extendPromptOptions = null): ?array
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
     * Connects a single file with another one.
     */
    public static function setSingleFileConnection(string|Model $sourceModel, string $field, array &$row, AbstractPromptImport $importer, ?array $extendPromptOptions = null): ?array
    {
        $source     = $row[$field];
        $connection = $field. '_connection';
        $fieldName  = $connection . '_' . $sourceModel::getTable() . '_' . $row['id'];

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
            $row[$field] = $connectedUuid;

            // Set connection
            $importer->addConnection($source, StringUtil::binToUuid($connectedUuid), FilesModel::getTable());
        }
        else
        {
            // Add a flash connection to display prompts for the same connections only once
            $importer->addFlashConnection($source, 1, $connection);

            $translator = Controller::getContainer()->get('translator');

            // Try to get the original image from archive
            if($fileStructure = $importer->getArchiveContentByFilename(FilesModel::getTable()))
            {
                $fileRows = \array_filter($fileStructure, function ($item) use ($row, $field) {
                    return $row[$field] === $item['uuid'];
                });

                $images = '';

                foreach ($fileRows ?? [] as $fileRow)
                {
                    // ToDo: SVGs needs a addition for the filetype ($fileRow['extension'] . '+xml'); use mime_content_type to get MIME types by filename.

                    $imageContent  = $importer->getArchiveContentByFilename($fileRow['path'], null, false, false);
                    $imageBase64   = 'data:image/' . $fileRow['extension'] . ';base64,' . base64_encode($imageContent);
                    $images       .= sprintf('<img src="%s" alt="original"/>', $imageBase64);
                }
            }

            // ToDo: Extend prompt options

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
                            'type'        => 'HTML',
                            'description' => $translator->trans('setup.prompt.content.singleSRC.explanation', [], 'setup'),
                            'content'     => $images ?? ''
                        ]
                    ]
                ]
            ];
        }

        return null;
    }
}
