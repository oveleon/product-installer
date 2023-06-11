<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\FormFieldModel;
use Contao\FormModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class FormFieldValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return FormFieldModel::getTable();
    }

    static function setFormConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $formStructure = $importer->getArchiveContentByTable(FormModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, FormFieldModel::getTable(), FormModel::getTable(), [
            'label'       => 'Formularfelder → Formular zuordnen',
            'description' => 'Ein oder mehrere Formularfelder konnten keinem Formular zugeordnet werden. Ihre Auswahl wird für alle weiteren Formularfelder, welche auf das selbe Formular referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer Formularfelder konnte das zugehörige Formular nicht gefunden werden. Wählen Sie bitte ein Formular aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen Formularfelder und einem Formular herzustellen.<br/><br/><b>Folgendes Formular wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $formStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
