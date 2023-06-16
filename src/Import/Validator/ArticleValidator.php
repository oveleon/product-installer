<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\Controller;
use Contao\PageModel;
use Contao\System;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Util\PageUtil;

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

    static public function getModel(): string
    {
        return ArticleModel::class;
    }

    /**
     * Treats the relationship with the parent element.
     */
    static function setPageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');
        $pages = PageModel::findAll(['order' => 'id ASC, sorting ASC']);

        /** @var PageUtil $pageUtil */
        /** @var PageUtil $pageUtil */
        $values = System::getContainer()
            ->get("Oveleon\ProductInstaller\Util\PageUtil")
            ->setPages($pages)
            ->getPagesSelectable(true);

        $pageStructure = $importer->getArchiveContentByFilename(PageModel::getTable(), [
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
            ]
        ], $values);
    }
}
