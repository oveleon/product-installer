<?php

namespace Oveleon\ProductInstaller\Util;

use Contao\Controller;
use Contao\ZipReader;

class ArchiveUtil
{
    public function getArchive($archivePath): ZipReader
    {
        return new ZipReader(str_replace(Controller::getContainer()->getParameter('kernel.project_dir'), '', $archivePath));
    }

    public function getFileContent($archivePath, $fileName, $parseJSON = false): ?array
    {
        $file = null;

        // Read zip archive
        $archive = $this->getArchive($archivePath);

        // Read file
        if($archive->getFile($fileName))
        {
            if($parseJSON)
                $file = json_decode($archive->unzip(), true);
            else
                $file = $archive->unzip();

            $archive->reset();
        }

        return $file;
    }

    public function getFileList(string $archivePath, string $fileExtension = null): ?array
    {
        // Read zip archive
        $archive = $this->getArchive($archivePath);
        $files = $archive->getFileList();

        if($fileExtension)
        {
            $filesWithExtension = [];

            foreach ($files as $file)
            {
                if(str_ends_with($file, $fileExtension))
                {
                    $filesWithExtension[] = $file;
                }
            }

            $files = $filesWithExtension;
        }

        return $files;
    }
}