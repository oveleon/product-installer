<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\CalendarModel;
use Contao\Controller;
use Contao\FaqCategoryModel;
use Contao\FormModel;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\NewsArchiveModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\ThemeModel;
use Doctrine\DBAL\Connection;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating the module records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ModuleValidator implements ValidatorInterface
{
    use ValidatorTrait;

    static public function getTrigger(): string
    {
        return ModuleModel::getTable();
    }

    static public function getModel(): string
    {
        return ModuleModel::class;
    }

    /**
     * Handles the relationship with the parent element.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setThemeConnection(array &$row, TableImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $themeStructure = $importer->getArchiveContentByFilename(ThemeModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, ModuleModel::getTable(), ThemeModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.module.theme.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.module.theme.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.module.theme.explanation', [], 'setup'),
                'content'     => $themeStructure ?? []
            ]
        ]);
    }

    /**
     * Determines from the ctm_id whether the module already exists. If this is the case, it is not imported, but
     * a connection to the module is established directly.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function isImportNecessary(array &$row, TableImport $importer): ?array
    {
        /** @var Connection $connection */
        $connection = System::getContainer()->get('doctrine.dbal.default_connection');

        // Check if a ctm_id field exists in our database table tl_module
        $hasCtmId = \in_array('ctm_id', \array_keys(
            $connection
                ->createSchemaManager()
                ->listTableColumns(
                    'tl_module'
                )
        ));

        if(
            !\array_key_exists('ctm_id', $row) ||
            !$hasCtmId ||
            !ModuleModel::countAll()
        )
        {
            return null;
        }

        $table = ModuleModel::getTable();

        if($record = ModuleModel::findOneBy(["$table.ctm_id=?"], [$row['ctm_id']]))
        {
            // Skip import (For validators in the BEFORE_IMPORT_ROW category, the following validators are skipped for this record)
            $row['_skip'] = true;

            // Add connection
            $importer->addConnection($row['id'], $record->id, $table);
        }

        return null;
    }

    /**
     * Handles the relationship with the field form for modules of type form.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setFormConnection(array &$row, TableImport $importer): ?array
    {
        if($row['type'] !== 'form' || !$importer->hasValue($row, 'form'))
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'       => $translator->trans('setup.prompt.module.form.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.module.form.description', [], 'setup'),
        ];

        return $importer->useIdentifierConnectionLogic($row, 'form', ModuleModel::getTable(), FormModel::getTable(), $promptOptions);
    }

    /**
     * Handles the relationship with the field reg_jumpTo.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setRegPageConnection(array &$row, TableImport $importer): ?array
    {
        if(!$importer->hasValue($row, 'reg_activate') || !$importer->hasValue($row, 'reg_jumpTo'))
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'reg_jumpTo', $row, $importer);
    }

    /**
     * Handles the relationship with the field pages.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setPagesConnection(array &$row, TableImport $importer): ?array
    {
        if(!$importer->hasValue($row, 'pages'))
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'pages', $row, $importer, ['multiple' => true]);
    }

    /**
     * Handles the relationship with the field rootPage.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setRootPageConnection(array &$row, TableImport $importer): ?array
    {
        if(!$importer->hasValue($row, 'defineRoot'))
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'rootPage', $row, $importer);
    }

    /**
     * Handles the relationship with the field overviewPage.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setOverviewPageConnection(array &$row, TableImport $importer): ?array
    {
        switch ($row['type'])
        {
            case 'faqreader':
            case 'newsreader':
            case 'newsletterreader':
                if(!$row['overviewPage'])
                {
                    return null;
                }

                break;
            default:
                return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'overviewPage', $row, $importer);
    }

    /**
     * Handles the relationship with the fields faq_categories.
     *
     * @category BEFORE_IMPORT_ROW
     */
    static function setArchiveConnections(array &$row, TableImport $importer, array $options = []): ?array
    {
        if (empty($options))
        {
            switch ($row['type'])
            {
                case 'faqlist':
                case 'faqreader':
                case 'faqpage':
                    $connectionField = 'faq_categories';
                    $connectionTable = FaqCategoryModel::getTable();
                    break;

                case 'newslist':
                case 'newsreader':
                case 'newsarchive':
                case 'newsmenu':
                    $connectionField = 'news_archives';
                    $connectionTable = NewsArchiveModel::getTable();
                    break;

                case 'calendar':
                case 'eventreader':
                case 'eventlist':
                case 'eventmenu':
                    $connectionField = 'cal_calendar';
                    $connectionTable = CalendarModel::getTable();
                    break;

                default:
                    if (isset($GLOBALS['PI_HOOKS']['setModuleValidatorArchiveConnections']) && \is_array($GLOBALS['PI_HOOKS']['setModuleValidatorArchiveConnections']))
                    {
                        foreach ($GLOBALS['PI_HOOKS']['setModuleValidatorArchiveConnections'] as $callback)
                        {
                            $options = System::importStatic($callback[0])->{$callback[1]}($row, $importer);

                            if (is_array($options) && isset($options['field']) || !isset($options['table']))
                            {
                                return self::setArchiveConnections($row, $importer, $options);
                            }
                        }
                    }

                    return null;
            }
        }
        elseif (isset($options['field']) && isset($options['table']))
        {
            $connectionField = $options['field'];
            $connectionTable = $options['table'];
        }
        else
        {
            return null;
        }

        if (!$row[$connectionField])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'              => $translator->trans('setup.prompt.module.'.$connectionField.'.label', [], 'setup'),
            'description'        => $translator->trans('setup.prompt.module.'.$connectionField.'.description', [], 'setup'),
            'multiple'           => true
        ];

        return $importer->useIdentifierConnectionLogic($row, $connectionField, ModuleModel::getTable(), $connectionTable, $promptOptions);
    }

    /**
     * Handles the relationship between modules among themselves (root_page_dependent_modules).
     *
     * @todo EXPERT-MODE: Connecting the root page dependent modules currently only works if pages are imported as well. To enable this case for the expert mode we need another validator which checks if these connections can be imported completely (willBeImported(tl_table) and if not, the pages have to be reassigned.
     *
     * @category AFTER_IMPORT_ROW
     *
     * @param array<ModuleModel, array> $collection
     */
    public static function setRootPageDependentModuleIncludes(array $collection, TableImport $importer): void
    {
        /** @var ModuleModel $model*/
        [$model, $row] = $collection;

        if($model->type !== 'root_page_dependent_modules')
        {
            return;
        }

        $moduleIds = null;

        foreach (StringUtil::deserialize($model->rootPageDependentModules, true) as $pageId => $moduleId)
        {
            if(
                ($connectedPageId   = $importer->getConnection($pageId, PageModel::getTable())) !== null &&
                ($connectedModuleId = $importer->getConnection($moduleId, $importer->getTable())) !== null
            )
            {
                $moduleIds[$connectedPageId] = $connectedModuleId;
            }
        }

        if($moduleIds)
        {
            $model->rootPageDependentModules = serialize($moduleIds);
            $model->save();
        }
    }
}
