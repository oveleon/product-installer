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
    private ?array $parameter = null;
    private ?string $header = null;

    /**
     * Set the method to be used.
     *
     * @param string $method
     *
     * @return $this
     */
    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set the header string which are passed on during the request.
     *
     * @param string $header
     *
     * @return $this
     */
    public function header(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Set parameters which are passed on during the request.
     *
     * @param array<string, string> $parameter
     *
     * @return $this
     */
    public function parameter(array $parameter): self
    {
        $this->parameter = $parameter;

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
            $context['http']['method'] = $this->method;
            $context['http']['header'] = $this->header;
        }

        if($this->parameter)
        {
            $context['http']['content'] = http_build_query($this->parameter);
        }

        if($context)
        {
            $context = stream_context_create($context);
        }

        $archive = new File($destination);
        $archive->write(file_get_contents($source, false, $context));
        $archive->close();
    }
}
