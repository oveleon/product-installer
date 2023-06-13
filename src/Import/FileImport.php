<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\File;
use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;

class FileImport extends AbstractPromptImport
{
    /**
     * Import files from manifest file.
     */
    public function importFromManifest($manifestFileName): ?AbstractPrompt
    {
        // Get manifest from archive
        $manifest = $this->getArchiveContentByFilename($manifestFileName);

        if(is_array($manifest['directories']))
        {
            foreach ($this->archiveUtil->getFileList($this->getArchive()) ?? [] as $filePath)
            {
                // Check if it is a folder and the filename starts with the base directory of my exports
                if(in_array(strtok($filePath, '/'), $manifest['directories']))
                {
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
     * Apply default table validators.
     */
    public static function useDefaultValidators(): void
    {
        // Apply default validators
        // Validator::useDefaultFileValidators();
    }
}
