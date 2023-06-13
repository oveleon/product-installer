<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\ContentModel;
use Contao\Controller;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the content records within news during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContentNewsValidator extends ContentValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ContentModel::getTable() . '.' . NewsModel::getTable();
    }

    /**
     * Deals with the relationship with the parent element.
     */
    static function setNewsConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        // Try to get the non-imported content to give the user a possibility to reference a similar content
        $newsStructure = $importer->getArchiveContentByFilename(NewsModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        // Get news archives for optgroups
        $values = [];
        $groups = [];

        foreach (NewsModel::findAll() ?? [] as $record)
        {
            /** @var NewsArchiveModel $archive */
            $archive = $record->getRelated('pid');

            $groups[] = [
                'label' => $archive->title,
                'value' => $record->pid
            ];

            $values[] = [
                'value' => $record->id,
                'text'  => $record->headline,
                'info'  => $record->id,
                'group' => $record->pid,
                'class' => 'news'
            ];
        }

        return $importer->useParentConnectionLogic($row, ContentModel::getTable(), NewsModel::getTable(), [
            'label'         => $translator->trans('setup.prompt.content.news.label', [], 'setup'),
            'description'   => $translator->trans('setup.prompt.content.news.description', [], 'setup'),
            'explanation'   => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.content.news.explanation', [], 'setup'),
                'content'     => $newsStructure ?? []
            ],
            'class'         => 'w50',
            'optgroupField' => 'group',
            'optgroups'     => $groups
        ], $values);
    }
}
