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
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

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
     */
    static function setThemeConnection(array &$row, AbstractPromptImport $importer): ?array
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
     * Handles the relationship with the field form for modules of type form.
     */
    public static function setFormConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if($row['type'] === 'form' || !$row['form'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'       => $translator->trans('setup.prompt.module.form.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.module.form.description', [], 'setup'),
        ];

        return $importer->useIdentifierConnectionLogic($row, 'form', LayoutModel::getTable(), FormModel::getTable(), $promptOptions);
    }

    /**
     * Handles the relationship with the field reg_jumpTo.
     */
    static function setRegPageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(!$row['reg_activate'] || !$row['reg_jumpTo'])
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'reg_jumpTo', $row, $importer);
    }

    /**
     * Handles the relationship with the field pages.
     */
    static function setPagesConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(null === $row['pages'])
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'pages', $row, $importer, ['multiple' => true]);
    }

    /**
     * Handles the relationship with the field rootPage.
     */
    static function setRootPageConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        if(!$row['defineRoot'])
        {
            return null;
        }

        return self::setFieldPageConnection(self::getModel(), 'rootPage', $row, $importer);
    }

    /**
     * Handles the relationship with the field overviewPage.
     */
    static function setOverviewPageConnection(array &$row, AbstractPromptImport $importer): ?array
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
     */
    static function setArchiveConnections(array &$row, AbstractPromptImport $importer): ?array
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
                return null;
        }

        if(!$row[$connectionField])
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
    public static function setRootPageDependentModuleIncludes(array $collection, AbstractPromptImport $importer): void
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
