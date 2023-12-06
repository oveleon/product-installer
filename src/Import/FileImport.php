<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\File;
use Contao\FilesModel;
use Contao\System;
use Symfony\Component\Filesystem\Filesystem;
use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;

/**
 * Class to import files.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FileImport extends AbstractPromptImport
{
    /**
     * Import directory files, which are also present in the manifest file.
     */
    public function importDirectoriesByManifest(string $manifestFileName, ?array $skipDirectories = null, ?array $allowedFileExtensions = null): ?AbstractPrompt
    {
        // Get manifest from archive
        $manifest = $this->getArchiveContentByFilename($manifestFileName);

        if(isset($manifest['directories']) && is_array($manifest['directories']))
        {
            // Strip directories to skip
            $directories = \array_filter($manifest['directories'], function ($directory) use ($skipDirectories) {
                return !in_array($directory, $skipDirectories);
            });

            return $this->importDirectoriesFromArchive($directories, $allowedFileExtensions);
        }

        return null;
    }

    /**
     * Import directory files, out of the archive from given directories.
     */
    public function importDirectoriesFromArchive(array $directories, ?array $allowedFileExtensions = null): ?AbstractPrompt
    {
        $root = System::getContainer()->getParameter('kernel.project_dir');
        $fs = new Filesystem();

        foreach ($this->archiveUtil->getFileList($this->getArchive()) ?? [] as $filePath)
        {
            $baseDirectory  = strtok($filePath, '/');
            $validExtension = true;

            if(null !== $allowedFileExtensions)
            {
                $validExtension = in_array(pathinfo($filePath, PATHINFO_EXTENSION), $allowedFileExtensions);
            }

            if(
                in_array($baseDirectory, $directories) &&
                $validExtension
            )
            {
                // ToDo: Parse validators and set prompt if needed

                $fs->dumpFile($root . DIRECTORY_SEPARATOR . $filePath, $this->archiveUtil->getFileContent($this->getArchive(), $filePath));
            }
        }

        return null;
    }

    /**
     * Imports a file based on an archive file and synchronizes the database.
     */
    public function importFileByPath($filePath): ?FilesModel
    {
        if($content = $this->archiveUtil->getFileContent($this->getArchive(), $filePath))
        {
            $file = new File($filePath);
            $file->write($content);
            $file->close();

            return $file->getModel();
        }

        return null;
    }
}
