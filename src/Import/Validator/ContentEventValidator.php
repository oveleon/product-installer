<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\CalendarEventsModel;
use Contao\ContentModel;
use Contao\Controller;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class ContentEventValidator extends ContentValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ContentModel::getTable() . '.' . CalendarEventsModel::getTable();
    }

    static function setEventConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $newsStructure = $importer->getArchiveContentByTable(CalendarEventsModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ContentModel::getTable(), CalendarEventsModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.content.event.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.content.event.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.content.event.explanation', [], 'setup'),
                'content'     => $newsStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
