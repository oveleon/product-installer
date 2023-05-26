<?php

namespace Oveleon\ProductInstaller\Download;

use Contao\File;

/**
 * Class for downloading files.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class FileDownloader
{
    /**
     * Download file.
     */
    public function download(string $source, string $destination): void
    {
        $archive = new File($destination);
        $archive->write(file_get_contents($source));
        $archive->close();
    }
}
