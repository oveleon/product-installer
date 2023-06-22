<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;
use Oveleon\ProductInstaller\SetupLock;
use Oveleon\ProductInstaller\Util\ArchiveUtil;

/**
 * Abstract class for various importers which work with prompts.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
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
     * Defines the file extension.
     */
    protected string $fileExtension;

    /**
     * The destination to the currently imported archive.
     */
    protected ?string $archiveDestination = null;

    /**
     * Abstract prompt import.
     */
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
     * Returns the archive destination.
     */
    public function getArchive(): string
    {
        return $this->archiveDestination;
    }

    /**
     * Sets the file extension.
     */
    public function setFileExtension(string $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Returns the file extension.
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /**
     * Returns the contents of a file or null if the file does not exist.
     *
     * $filter:   Filter the content rows
     * - field:   The field to filter on
     * - value:   The value that must exist in the field
     *
     * - keys:    Filters the keys and retains only keys that have been handed over
     */
    public function getArchiveContentByFilename(string $fileName, array $filter = null, $parseJSON = true, $extendFileExtension = true): null|array|string
    {
        $content = $this->archiveUtil->getFileContent($this->archiveDestination, $fileName . ($extendFileExtension ? '.' . $this->fileExtension : ''), $parseJSON);

        if($content && null !== $filter)
        {
            // field-value filter
            if(\array_key_exists('field', $filter) && \array_key_exists('value', $filter))
            {
                if(\is_array($filter['value']))
                {
                    $content = \array_filter($content, function ($item) use ($filter) {
                        return \in_array($item[ $filter['field'] ], $filter['value']);
                    });
                }
                else
                {
                    $content = \array_filter($content, function ($item) use ($filter) {
                        return $item[ $filter['field'] ] === $filter['value'];
                    });
                }
            }

            if(\array_key_exists('keys', $filter) && $content)
            {
                foreach ($content as &$row)
                {
                    $row = \array_intersect_key($row, array_flip($filter['keys']));
                }
            }

            if(!empty($content) && !\is_array($filter['value']))
            {
                $content = \current($content);
            }
        }

        return $content;
    }
}
