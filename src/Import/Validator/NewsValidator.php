<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FilesModel;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating the news records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class NewsValidator implements ValidatorInterface
{
    use ValidatorTrait;

    static public function getTrigger(): string
    {
        return NewsModel::getTable();
    }

    static public function getModel(): string
    {
        return NewsModel::class;
    }

    /**
     * Handles the relationship with the parent element.
     */
    static function setNewsArchiveConnection(array &$row, TableImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $newsArchiveStructure = $importer->getArchiveContentByFilename(NewsArchiveModel::getTable(), [
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
            ]
        ]);
    }

    /**
     * Handles news image (singleSRC) in news elements.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setNewsImageConnection(array &$row, TableImport $importer): ?array
    {
        if(!$importer->hasValue($row, 'singleSRC'))
        {
            return null;
        }

        // Get translator
        $translator = Controller::getContainer()->get('translator');

        return $importer->useIdentifierConnectionLogic($row, 'singleSRC', NewsModel::getTable(), FilesModel::getTable(), [
            'class'       => 'w50',
            'isFile'      => true,
            'widget'      => FormPromptType::FILE,
            'popupTitle'  => $translator->trans('setup.prompt.news.singleSRC.title', [], 'setup'),
            'label'       => $translator->trans('setup.prompt.news.singleSRC.title', [], 'setup'),
            'description' => $translator->trans('setup.prompt.news.singleSRC.description', [], 'setup'),
            'explanation' => self::getFileExplanationClosure(
                $row,
                'singleSRC',
                $importer,
                $translator->trans('setup.prompt.news.singleSRC.explanation', [], 'setup')
            )
        ]);
    }
}
