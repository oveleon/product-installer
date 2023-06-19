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
    use ValidatorTrait;

    static public function getTrigger(): string
    {
        return ModuleModel::getTable();
    }

    static public function getModel(): string
    {
        return ModuleModel::class;
    }

    /**
     * Treats the relationship with the parent element.
     */
    static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $themeStructure = $importer->getArchiveContentByFilename(ThemeModel::getTable(), [
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
            ]
        ]);
    }

    /**
     * Treats the relationship with the field reg_jumpTo.
     */
    static function setRegPageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        return self::setFieldPageConnection(self::getModel(), 'reg_jumpTo', $row, $importer);
    }

    /**
     * Treats the relationship with the field pages.
     */
    static function setPagesConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        return self::setFieldPageConnection(self::getModel(), 'pages', $row, $importer, ['multiple' => true]);
    }

    /**
     * Treats the relationship with the field rootPage.
     */
    static function setRootPageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(!$row['defineRoot'])
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'rootPage', $row, $importer);
    }
}
