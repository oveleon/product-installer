<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\LayoutModel;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

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
     * Handles the relationship with file-connection for the field `external` and ´externalJs`.
     */
    public static function setExternalFileConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        // ToDo: Handle multiple file-connections (serialized).
    }
}
