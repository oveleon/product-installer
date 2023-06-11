<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ModuleModel;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class ModuleValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ModuleModel::getTable();
    }

    static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $themeStructure = $importer->getArchiveContentByTable(ThemeModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ModuleModel::getTable(), ThemeModel::getTable(), [
            'label'       => 'Module → Theme zuordnen',
            'description' => 'Ein oder mehrere Module konnten keinem Theme zugeordnet werden. Ihre Auswahl wird für alle weiteren Module, welche auf das selbe Theme referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer Module konnte das zugehörige Theme nicht gefunden werden. Wählen Sie bitte ein Theme aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen Modulen und einem Theme herzustellen.<br/><br/><b>Folgendes Theme wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $themeStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
