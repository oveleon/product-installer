<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\LayoutModel;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class LayoutValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return LayoutModel::getTable();
    }

    static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $themeStructure = $importer->getArchiveContentByTable(ThemeModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, LayoutModel::getTable(), ThemeModel::getTable(), [
            'label'       => 'Layout → Theme zuordnen',
            'description' => 'Ein oder mehrere Layouts konnten keinem Theme zugeordnet werden. Ihre Auswahl wird für alle weiteren Layouts, welche auf das selbe Theme referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer Layouts konnte das zugehörige Theme nicht gefunden werden. Wählen Sie bitte ein Theme aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen Layouts und einem Theme herzustellen.<br/><br/><b>Folgendes Theme wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $themeStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
