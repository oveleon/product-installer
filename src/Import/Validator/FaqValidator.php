<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the faq records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FaqValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return FaqModel::getTable();
    }

    /**
     * Deals with the relationship with the parent element.
     */
    static function setFaqCategoryConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $faqCategoryStructure = $importer->getArchiveContentByTable(FaqCategoryModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, FaqModel::getTable(), FaqCategoryModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.faq.category.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.faq.category.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.faq.category.explanation', [], 'setup'),
                'content'     => $faqCategoryStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
