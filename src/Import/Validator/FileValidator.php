<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;
use Oveleon\ProductInstaller\Import\FileImport;

/**
 * Validator class for validating the file records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FileValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return FilesModel::getTable();
    }

    /**
     * Creates a file from row.
     */
    static function createFile(array &$row, AbstractPromptImport $importer): ?array
    {
        if($row['type'] === 'file')
        {
            /** @var FileImport $fileImporter */
            $fileImporter = System::getContainer()->get('Oveleon\ProductInstaller\Import\FileImport');
            $fileImporter->setArchive($importer->getArchive());

            /** @var FilesModel $fileModel */
            if($fileModel = $fileImporter->importFileByPath($row['path']))
            {
                $importer->addConnection($row['uuid'], StringUtil::binToUuid($fileModel->uuid));
            }
        }

        // Skip import, because the database has already been updated by the file creation
        $row['_skip'] = true;

        return null;
    }
}
