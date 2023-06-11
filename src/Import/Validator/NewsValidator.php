<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class NewsValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return NewsModel::getTable();
    }

    static function setNewsArchiveConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $newsArchiveStructure = $importer->getArchiveContentByTable(NewsArchiveModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, NewsModel::getTable(), NewsArchiveModel::getTable(), [
            'label'       => 'News → News-Archiv zuordnen',
            'description' => 'Ein oder mehrere News konnten keinem News-Archive zugeordnet werden. Ihre Auswahl wird für alle weiteren News, welche auf das selbe News-Archiv referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer News konnte das zugehörige News-Archiv nicht gefunden werden. Wählen Sie bitte ein News-Archiv aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen News und einem News-Archiv herzustellen.<br/><br/><b>Folgendes News-Archiv wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $newsArchiveStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
