<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\ModuleModel;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the module records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ModuleValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ModuleModel::getTable();
    }

    /**
     * Deals with the relationship with the parent element.
     */
    static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $themeStructure = $importer->getArchiveContentByTable(ThemeModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ModuleModel::getTable(), ThemeModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.module.theme.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.module.theme.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.module.theme.explanation', [], 'setup'),
                'content'     => $themeStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
