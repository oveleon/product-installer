<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\Controller;
use Contao\PageModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the article records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ArticleValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ArticleModel::getTable();
    }

    /**
     * Deals with the relationship with the parent element.
     */
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
