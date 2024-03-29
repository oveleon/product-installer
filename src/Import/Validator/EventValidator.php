<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Controller;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating the event records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class EventValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return CalendarEventsModel::getTable();
    }

    static public function getModel(): string
    {
        return CalendarEventsModel::class;
    }

    /**
     * Handles the relationship with the parent element.
     */
    static function setEventArchiveConnection(array &$row, TableImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $calendarStructure = $importer->getArchiveContentByFilename(CalendarModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, CalendarEventsModel::getTable(), CalendarModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.event.archive.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.event.archive.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.event.archive.explanation', [], 'setup'),
                'content'     => $calendarStructure ?? []
            ]
        ]);
    }
}
