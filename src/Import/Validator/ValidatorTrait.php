<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Util\PageUtil;

/**
 * Validator trait for recurring actions.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
trait ValidatorTrait
{
    /**
     * Connects the specified field of the passed source models to a new page.
     */
    public static function setFieldPageConnection(string|Model $sourceModel, string $field, array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = System::getContainer()->get('translator');
        $pages = PageModel::findAll(['order' => 'id ASC, sorting ASC']);

        /** @var PageUtil $pageUtil */
        $values = System::getContainer()
            ->get("Oveleon\ProductInstaller\Util\PageUtil")
            ->setPages($pages)
            ->getPagesSelectable(true);

        $missingStructure = $importer->getArchiveContentByFilename(PageModel::getTable(), [
            'value' => $row[$field],
            'field' => 'id'
        ]);

        $translatorNamePart = str_replace("tl_", "", $sourceModel::getTable());

        return $importer->useIdentifierConnectionLogic($row, $field, $sourceModel::getTable(), PageModel::getTable(), [
            'label'         => $translator->trans('setup.prompt.'.$translatorNamePart.'.page.label', [], 'setup'),
            'description'   => $translator->trans('setup.prompt.'.$translatorNamePart.'.page.description', [], 'setup'),
            'explanation'   => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.'.$translatorNamePart.'.page.explanation', [], 'setup'),
                'content'     => $missingStructure ?? []
            ]
        ], $values);
    }
}
