<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class FaqValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return FaqModel::getTable();
    }

    static function setFaqCategoryConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $faqCategoryStructure = $importer->getArchiveContentByTable(FaqCategoryModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, FaqModel::getTable(), FaqCategoryModel::getTable(), [
            'label'       => 'FAQ → FAQ-Kategorie zuordnen',
            'description' => 'Ein oder mehrere FAQs konnten keiner FAQ-Kategorie zugeordnet werden. Ihre Auswahl wird für alle weiteren FAQs, welche auf die selbe FAQ-Kategorie referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer FAQs konnte die zugehörige FAQ-Kategorie nicht gefunden werden. Wählen Sie bitte eine FAQ-Kategorie aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen FAQs und einer FAQ-Kategorie herzustellen.<br/><br/><b>Folgende FAQ-Kategorie wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $faqCategoryStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
