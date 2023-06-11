<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\PageModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class ArticleValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ArticleModel::getTable();
    }

    static function setPageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        // ToDo: Get page structure for select

        $pageStructure = $importer->getArchiveContentByTable(PageModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ArticleModel::getTable(), PageModel::getTable(), [
            'label'       => 'Artikel → Seite zuordnen',
            'description' => 'Ein oder mehrere Artikel konnten keiner Seite zugeordnet werden. Ihre Auswahl wird für alle weiteren Artikel, welche auf die selbe Seite referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer Artikel konnte die zugehörige Seite nicht gefunden werden. Wählen Sie bitte eine Seite aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen Artikeln und einer Seite herzustellen.<br/><br/><b>Folgende Seite wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $pageStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
