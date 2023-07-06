<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\System;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Util\PageUtil;

/**
 * Validator class for validating the content records within articles during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContentArticleValidator extends ContentValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ContentModel::getTable() . '.' . ArticleModel::getTable();
    }

    static public function getModel(): string
    {
        return ContentModel::class;
    }

    /**
     * Handles the relationship with the parent element.
     */
    static function setArticleConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        /** @var PageUtil $pageUtil */
        $values = System::getContainer()
            ->get("Oveleon\ProductInstaller\Util\PageUtil")
            ->setPages()
            ->setArticles()
            ->getArticleSelectable();

        $articleStructure = $importer->getArchiveContentByFilename(ArticleModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ContentModel::getTable(), ArticleModel::getTable(), [
            'class'       => 'w50',
            'label'       => $translator->trans('setup.prompt.content.article.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.content.article.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.content.article.explanation', [], 'setup'),
                'content'     => $articleStructure ?? []
            ]
        ], $values);
    }
}
