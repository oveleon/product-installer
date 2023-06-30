<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\ThemeModel;
use Contao\FilesModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;

/**
 * Validator class for validating the layout records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class LayoutValidator implements ValidatorInterface
{
    public static function getTrigger(): string
    {
        return LayoutModel::getTable();
    }

    public static function getModel(): string
    {
        return LayoutModel::class;
    }

    /**
     * Handles the relationship with the parent element.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $themeStructure = $importer->getArchiveContentByFilename(ThemeModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, LayoutModel::getTable(), ThemeModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.layout.theme.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.layout.theme.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.layout.theme.explanation', [], 'setup'),
                'content'     => $themeStructure ?? []
            ]
        ]);
    }

    /**
     * Handles the relationship with the field modules.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setModuleConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(!$row['modules'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');
        $originalModules = StringUtil::deserialize($row['modules'], true);
        $modules = [];

        // Create array with the module id as the key and clean duplicate article rows for same column
        foreach ($originalModules as $module)
        {
            if($moduleId = $module['mod'])
                $modules[ $moduleId ] = $module;
            else
                $modules[ $module['col'] ] = $module;
        }

        // Filter module ids
        $moduleCollection = array_filter($modules, fn ($key) => is_numeric($key), ARRAY_FILTER_USE_KEY);

        // Generate selectable values once to save performance
        $values = null;

        if($records = ModuleModel::findAll())
        {
            foreach ($records as $record)
            {
                $values[] = [
                    'value' => $record->id,
                    'text'  => html_entity_decode($record->name),
                    'info'  => $record->id,
                    'class' => 'module'
                ];
            }
        }

        $fieldOverwrites = null;
        $fieldCollection = null;

        // Check for each module individually and generate a field if necessary
        foreach ($moduleCollection as $module)
        {
            $moduleId  = $module['mod'];
            $fieldName = 'modules_' . $row['id'] . '_' . $moduleId;

            if(
                ($connectedId = $importer->getConnection($moduleId, ModuleModel::getTable())) ||
                ($connectedId = $importer->getPromptValue($fieldName)) !== null
            )
            {
                // Connect modules and overwrite the field
                $fieldOverwrites[$moduleId] = $connectedId;
            }
            else
            {
                $fieldCollection[$fieldName] = [
                    $values ?? [],
                    FormPromptType::SELECT,
                    [
                        'class'         => 'w50',
                        'label'         => $translator->trans('setup.prompt.layout.modules.label', ['%col%' => $module['col']], 'setup'),
                        'description'   => $translator->trans('setup.prompt.layout.modules.description', [], 'setup'),
                    ]
                ];
            }
        }

        // Overwrite field values with associated values, if these are set
        if($fieldOverwrites)
        {
            foreach ($fieldOverwrites as $prevId => $nextId)
            {
                if($module = $modules[$prevId])
                {
                    // Overwrite with new id
                    $module['mod'] = $nextId;

                    // Overwrite module array
                    $modules[$prevId] = $module;
                }
            }

            $row['modules'] = serialize(array_values($modules));
        }

        // Add fieldset for better overview
        if($fieldCollection)
        {
            // Start
            $fieldCollection = ['fieldset_start_' . $row['id'] => [
                [],
                FormPromptType::FIELDSET,
                [
                    'label' => $translator->trans('setup.prompt.layout.modules.fieldset', [], 'setup'),
                    'start' => true,
                    'explanation'   => [
                        'type'        => 'TABLE',
                        'description' => $translator->trans('setup.prompt.layout.modules.explanation', [], 'setup'),
                        'content'     => $originalModules
                    ],
                ]
            ]] + $fieldCollection;

            // End
            $fieldCollection['fieldset_end_' . $row['id']] = [
                [],
                FormPromptType::FIELDSET
            ];
        }

        return $fieldCollection;
    }

    /**
     * Handles the relationship with file-connection for the field `external`.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setExternalFileConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(!$row['external'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'              => $translator->trans('setup.prompt.layout.external.label', [], 'setup'),
            'description'        => $translator->trans('setup.prompt.layout.external.description', [], 'setup'),
            'multiple'           => true,
            'isFile'             => true,
            'widget'             => FormPromptType::FILE,
            'allowedExtensions'  => 'css,scss'
        ];

        return $importer->useIdentifierConnectionLogic($row, 'external', LayoutModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }

    /**
     * Handles the relationship with file-connection for the field ´externalJs`.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setExternalJsFileConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(!$row['externalJs'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'             => $translator->trans('setup.prompt.layout.externalJs.label', [], 'setup'),
            'description'       => $translator->trans('setup.prompt.layout.externalJs.description', [], 'setup'),
            'multiple'          => true,
            'isFile'            => true,
            'widget'            => FormPromptType::FILE,
            'allowedExtensions' => 'js'
        ];

        return $importer->useIdentifierConnectionLogic($row, 'externalJs', LayoutModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }
}
