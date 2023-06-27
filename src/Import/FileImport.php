<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\File;
use Contao\FilesModel;
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
     *
     * ToDo: Validators and the ability to send prompts need to be developed.
     */
    public function importDirectoriesByManifest(string $manifestFileName, array $skipDirectories = ['files']): ?AbstractPrompt
    {
        // Get manifest from archive
        $manifest = $this->getArchiveContentByFilename($manifestFileName);

        if(is_array($manifest['directories']))
        {
            foreach ($this->archiveUtil->getFileList($this->getArchive()) ?? [] as $filePath)
            {
                $baseDirectory = strtok($filePath, '/');

                // Check if it is a folder and the file name starts with the base directory of my exports and the directory should not be skipped
                if(
                     in_array($baseDirectory, $manifest['directories']) &&
                    !in_array($baseDirectory, $skipDirectories)
                )
                {
                    // ToDo: Parse validators and set prompt if needed
                    // Fixme: Create file with Symfony to avoid overhead, e.g. dbafs / database

                    // Create file
                    $archive = new File($filePath);
                    $archive->write($this->archiveUtil->getFileContent($this->getArchive(), $filePath));
                    $archive->close();
                }
            }
        }

        return null;
    }

    /**
     * Imports a file based on an archive file and synchronizes the database.
     */
    public function importFileByPath($filePath): ?FilesModel
    {
        // ToDo: Allow empty files like .public
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
