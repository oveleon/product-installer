<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FormFieldModel;
use Contao\FormModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

class FormFieldValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return FormFieldModel::getTable();
    }

    static function setFormConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $formStructure = $importer->getArchiveContentByTable(FormModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, FormFieldModel::getTable(), FormModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.form_field.form.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.form_field.form.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.form_field.form.explanation', [], 'setup'),
                'content'     => $formStructure ?? []
            ],
            'class'       => 'w50'
        ]);
    }
}
