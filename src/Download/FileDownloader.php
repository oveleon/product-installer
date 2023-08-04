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
    private string $method = 'GET';
    private ?string $header = null;

    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function header(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Download file.
     */
    public function download(string $source, string $destination): void
    {
        $context = null;

        if($this->header)
        {
            $context = stream_context_create(['http' => [
                'method'  => $this->method,
                'header'  => $this->header
            ]]);
        }

        $archive = new File($destination);
        $archive->write(file_get_contents($source, false, $context));
        $archive->close();
    }
}
