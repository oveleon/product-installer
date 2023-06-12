<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\Controller;
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

        $translator = Controller::getContainer()->get('translator');

        $pageStructure = $importer->getArchiveContentByTable(PageModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ArticleModel::getTable(), PageModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.article.page.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.article.page.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.article.page.explanation', [], 'setup'),
                'content'     => $pageStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
