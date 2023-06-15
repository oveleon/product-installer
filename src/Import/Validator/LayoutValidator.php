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
    static public function getTrigger(): string
    {
        return LayoutModel::getTable();
    }

    /**
     * Deals with the relationship with the parent element.
     */
    static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
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
}
