<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;
use Oveleon\ProductInstaller\Setup\ContentPackageSetup;
use Oveleon\ProductInstaller\SetupLock;
use Oveleon\ProductInstaller\Util\ArchiveUtil;

abstract class AbstractPromptImport
{
    /**
     * Defines the current prompt.
     */
    protected ?AbstractPrompt $prompt = null;

    /**
     * Contains the PromptResponse.
     */
    protected PromptResponse $promptResponse;

    /**
     * The destination to the currently imported archive.
     */
    protected ?string $archiveDestination = null;

    public function __construct(
        protected readonly SetupLock $setupLock,
        protected readonly ArchiveUtil $archiveUtil,
    ){}

    /**
     * Returns a PromptResponse by name.
     */
    public function getPromptResponse(): PromptResponse
    {
        return $this->promptResponse;
    }

    /**
     * Sets a PromptResponse.
     */
    public function setPromptResponse(PromptResponse $promptResponse): void
    {
        $this->promptResponse = $promptResponse;
    }

    /**
     * Sets the setup scope.
     */
    public function setScope(string $scope)
    {
        $this->setupLock->setScope($scope);
    }

    /**
     * Sets the prompt to be returned.
     */
    public function setPrompt(AbstractPrompt $prompt): void
    {
        $this->prompt = $prompt;
    }

    /**
     * Sets the archive destination.
     */
    public function setArchive(string $destination): void
    {
        $this->archiveDestination = $destination;
    }

    /**
     * Returns the contents of a file or null if the file does not exist.
     *
     * $filter:   Filter the content rows
     * - field:   The field to filter on
     * - value:   The value that must exist in the field
     */
    public function getArchiveContentByTable(string $file, array $filter = null, $parseJSON = true): ?array
    {
        $content = $this->archiveUtil->getFileContent($this->archiveDestination, $file . ContentPackageSetup::TABLE_FILE_EXTENSION, $parseJSON);

        if($content && null !== $filter)
        {
            $content = array_filter($content, function ($item) use ($filter) {
                return $item[ $filter['field'] ] === $filter['value'];
            });

            if(!empty($content))
            {
                $content = current($content);
            }
        }

        return $content;
    }
}
