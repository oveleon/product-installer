<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Controller;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

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

        $articleStructure = $importer->getArchiveContentByTable(ArticleModel::getTable(), [
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
            ],
            'class'       => 'w50'
        ]);
    }
}
