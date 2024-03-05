<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\CalendarFeedModel;
use Contao\CalendarModel;
use Contao\Controller;
use Contao\FaqCategoryModel;
use Contao\FormModel;
use Contao\NewsArchiveModel;
use Contao\NewsletterModel;
use Contao\System;
use Contao\UserGroupModel;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating the user groups records during and after import.
 */
class UserGroupValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return UserGroupModel::getTable();
    }

    static public function getModel(): string
    {
        return UserGroupModel::class;
    }

    /**
     * Handles the relationship with archives
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setArchiveConnections(array &$row, TableImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');
        $fieldCollection = [];
        $validTables = [
            'forms' => FormModel::getTable(),
            'faqs' => FaqCategoryModel::getTable(),
            'news' => NewsArchiveModel::getTable(),
            'calendars' => CalendarModel::getTable(),
            'calendarfeeds' => CalendarFeedModel::getTable(),
            'newsletters' => NewsletterModel::getTable()
        ];

        // Hook for expanding valid tables
        if (
            isset($GLOBALS['PI_HOOKS']['setUserGroupValidatorArchiveConnections']) &&
            \is_array($GLOBALS['PI_HOOKS']['setUserGroupValidatorArchiveConnections'])
        ) {
            foreach ($GLOBALS['PI_HOOKS']['setUserGroupValidatorArchiveConnections'] as $callback)
            {
                System::importStatic($callback[0])->{$callback[1]}($validTables, $row, $importer);
            }
        }

        $validKeys = array_intersect(array_keys($row), array_keys($validTables));

        foreach ($validKeys as $key)
        {
            $table = $validTables[$key];

            if (!$importer->hasValue($row, $key))
            {
                continue;
            }

            $promptOptions = [
                'label'       => $translator->trans('setup.prompt.user_group.'.$key.'.label', ['%userGroupName%' => $row['name']], 'setup'),
                'description' => $translator->trans('setup.prompt.user_group.'.$key.'.description', [], 'setup'),
                'multiple'    => true
            ];

            if($promptFields = $importer->useIdentifierConnectionLogic($row, $key, UserGroupModel::getTable(), $table, $promptOptions))
            {
                $fieldCollection = $fieldCollection + $promptFields;
            }
        }

        return $fieldCollection;
    }
}
