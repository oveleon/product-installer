<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class EventValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return CalendarEventsModel::getTable();
    }

    static function setEventArchiveConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $calendarStructure = $importer->getArchiveContentByTable(CalendarModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, CalendarEventsModel::getTable(), CalendarModel::getTable(), [
            'label'       => 'Events → Event-Archive zuordnen',
            'description' => 'Ein oder mehrere Events konnten keinem Event-Archive zugeordnet werden. Ihre Auswahl wird für alle weiteren Events, welche auf das selbe Event-Archiv referenzieren, übernommen.',
            'explanation' => [
                'type'        => 'TABLE',
                'description' => 'Beim Importieren eines oder mehrerer Events konnte das zugehörige Event-Archiv nicht gefunden werden. Wählen Sie bitte ein Event-Archiv aus Ihrer Contao-Instanz, um eine Verknüpfung zwischen diesen Events und einem Event-Archiv herzustellen.<br/><br/><b>Folgendes Event-Archiv wurde nicht importiert und benötigt ein Alternative:</b>',
                'content'     => $calendarStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
