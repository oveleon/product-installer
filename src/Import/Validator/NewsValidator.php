<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
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
        $translator = Controller::getContainer()->get('translator');

        $newsArchiveStructure = $importer->getArchiveContentByTable(NewsArchiveModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, NewsModel::getTable(), NewsArchiveModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.news.archive.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.news.archive.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.news.archive.explanation', [], 'setup'),
                'content'     => $newsArchiveStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
