<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Contao\PageModel;
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

    /**
     * Deals with the relationship with the parent element.
     */
    static function setArticleConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $values   = [];
        $pages    = PageModel::findAll(['order' => 'id ASC, sorting ASC']);
        $articles = ArticleModel::findAll();

        /** @var PageUtil $pageUtil */
        $pageUtil = System::getContainer()
            ->get("Oveleon\ProductInstaller\Util\PageUtil")
            ->setPages($pages)
            ->setArticles($articles);

        foreach ($pageUtil->getArticlesFlat() as $pageArticles)
        {
            $values[] = [
                'value'     => $pageArticles['id'],
                'text'      => $pageArticles['title'],
                'class'     => ($pageArticles['_isArticle'] ?? false) ? ($pageArticles['published'] ? 'article' : 'article_inv') : $pageArticles['type'],
                'info'      => ($pageArticles['_isArticle'] ?? false) ? $pageArticles['id'] : '',
                'group'     => 'page',
                'level'     => $pageArticles['_level'],
                'disabled'  => !($pageArticles['_isArticle'] ?? false),
            ];
        }

        $articleStructure = $importer->getArchiveContentByFilename(ArticleModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ContentModel::getTable(), ArticleModel::getTable(), [
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
