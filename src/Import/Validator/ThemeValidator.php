<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\ThemeModel;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating theme records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ThemeValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return ThemeModel::getTable();
    }

    static public function getModel(): string
    {
        return ThemeModel::class;
    }

    /**
     * Handles the relationship with file-connection for the field ´screenshot`.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setScreenshotConnection(array &$row, TableImport $importer): ?array
    {
        if(!$row['screenshot'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'             => $translator->trans('setup.prompt.theme.screenshot.label', [], 'setup'),
            'description'       => $translator->trans('setup.prompt.theme.screenshot.description', [], 'setup'),
            'isFile'            => true,
            'widget'            => FormPromptType::FILE
        ];

        return $importer->useIdentifierConnectionLogic($row, 'screenshot', ThemeModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }


    /**
     * Handles the relationship with file-connection for the field ´skinSourceFiles`.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setSkinSourceFilesConnection(array &$row, TableImport $importer): ?array
    {
        if(!$row['skinSourceFiles'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'             => $translator->trans('setup.prompt.theme.skinSourceFiles.label', [], 'setup'),
            'description'       => $translator->trans('setup.prompt.theme.skinSourceFiles.description', [], 'setup'),
            'multiple'          => true,
            'isFile'            => true,
            'widget'            => FormPromptType::FILE,
            'allowedExtensions' => 'css,scss,less'
        ];

        return $importer->useIdentifierConnectionLogic($row, 'skinSourceFiles', ThemeModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }

    /**
     * Handles the relationship with file-connections for the field `folders`.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setFolderConnection(array &$row, TableImport $importer): ?array
    {
        if(!$row['folders'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'              => $translator->trans('setup.prompt.theme.folders.label', [], 'setup'),
            'description'        => $translator->trans('setup.prompt.theme.folders.description', [], 'setup'),
            'multiple'           => true,
            'isFile'             => true,
            'allowedExtensions'  => '', // Pass empty string for folders
            'widget'             => FormPromptType::FILE
        ];

        // In order to use the method useIdentifierConnectionLogic, we must first convert the paths to uuids and set a
        // corresponding connection.
        $folders = [];

        foreach (StringUtil::deserialize($row['folders'], true) as $folderPath)
        {
            if($file = FilesModel::findByPath($folderPath))
            {
                // Add uuid as folder
                $folders[] = $uuid = StringUtil::binToUuid($file->uuid);

                // Add connection for the folder uuid
                $importer->addConnection($uuid, $uuid, FilesModel::getTable());
            }
        }

        // Overwrite paths with uuids
        $row['folders'] = serialize($folders);

        return $importer->useIdentifierConnectionLogic($row, 'folders', ThemeModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }

    /**
     * Handles the relationship with file-connections for the field `outputFilesTargetDir`.
     *
     * @category BEFORE_IMPORT_ROW
     */
    public static function setSkinFolderConnection(array &$row, TableImport $importer): ?array
    {
        if(!$row['outputFilesTargetDir'])
        {
            return null;
        }

        $translator = Controller::getContainer()->get('translator');

        $promptOptions = [
            'label'              => $translator->trans('setup.prompt.theme.outputFilesTargetDir.label', [], 'setup'),
            'description'        => $translator->trans('setup.prompt.theme.outputFilesTargetDir.description', [], 'setup'),
            'isFile'             => true,
            'allowedExtensions'  => '', // Pass empty string for folders
            'widget'             => FormPromptType::FILE
        ];

        // In order to use the method useIdentifierConnectionLogic, we must first convert the paths to uuids and set a
        // corresponding connection.
        if($file = FilesModel::findByPath($row['outputFilesTargetDir']))
        {
            // Overwrite path with uuids
            $row['outputFilesTargetDir'] = $uuid = StringUtil::binToUuid($file->uuid);

            // Add connection for the folder uuid
            $importer->addConnection($uuid, $uuid, FilesModel::getTable());
        }
        else
        {
            return null;
        }

        return $importer->useIdentifierConnectionLogic($row, 'outputFilesTargetDir', ThemeModel::getTable(), FilesModel::getTable(), $promptOptions, []);
    }
}
