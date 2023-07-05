<?php

namespace Oveleon\ProductInstaller;

use Contao\ArticleModel;
use Contao\CalendarEventsModel;
use Contao\CalendarFeedModel;
use Contao\ContentModel;
use Contao\FaqModel;
use Contao\FilesModel;
use Contao\FormModel;
use Contao\ModuleModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\UserModel;

/**
 * Class to represent an InsertTag and facilitate the editing of it.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class InsertTag
{
    protected ?string $command = null;
    protected null|string|InsertTag $value = null;
    protected ?array  $flags = null;
    protected ?string $query = null;

    /**
     * Load an insert tag from a string.
     */
    public function fromString(string $buffer): void
    {
        // Clean whitespaces
        $buffer = trim($buffer);

        // Clean outer markup
        if(str_starts_with($buffer, '{{') && str_ends_with($buffer, '}}'))
        {
            $buffer = substr($buffer, 2, -2);
        }

        $this->parse($buffer);
    }

    /**
     * Returns the insert tag as an insert tag string.
     */
    public function toString(): string
    {
        $value = $this->value;

        if($this->value instanceof InsertTag)
        {
            $value = $this->value->toString();
        }

        if($value && $this->query)
        {
            return sprintf('{{%s::%s?%s|%s}}', $this->command, $value, $this->query, implode('|', $this->flags));
        }
        elseif($value && $this->flags)
        {
            return sprintf('{{%s::%s|%s}}', $this->command, $value, implode('|', $this->flags));
        }
        elseif($value)
        {
            return sprintf('{{%s::%s}}', $this->command, $value);
        }

        return sprintf('{{%s}}', $this->command);
    }

    /**
     * Returns the parts of an InsertTag as array.
     */
    public function toArray(): array
    {
        $value = $this->value;

        if($this->value instanceof InsertTag)
        {
            $value = $this->value->getValue();
        }

        return [
            $this->command,
            $value,
            $this->query,
            $this->flags
        ];
    }

    /**
     * Returns the command.
     */
    public function getCommand($prefixOnly = false): string
    {
        if($prefixOnly)
        {
            return strtok($this->command, '_');
        }

        return $this->command;
    }

    /**
     * Set the command.
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * Returns the flags.
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * Set the flags.
     */
    public function setFlags(array $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * Returns the value.
     */
    public function getValue(): string|InsertTag
    {
        return $this->value;
    }

    /**
     * Set the value.
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Returns the related table of the insert tag.
     */
    public function getRelatedTable(): ?string
    {
        if(!$this->command)
        {
            return null;
        }

        return match ($this->getCommand())
        {
            'insert_article',
            'article_teaser' => ArticleModel::getTable(),
            'insert_content' => ContentModel::getTable(),
            'insert_module'  => ModuleModel::getTable(),
            'insert_form'    => FormModel::getTable(),
            'news_teaser'    => NewsModel::getTable(),
            'event_teaser'   => CalendarEventsModel::getTable(),
            'picture',
            'figure',
            'image',
            'file'           => FilesModel::getTable(),

            default          => match ($this->getCommand(true))
            {
                'link'       => PageModel::getTable(),
                'article'    => ArticleModel::getTable(),
                'news'       => NewsModel::getTable(),
                'event'      => CalendarEventsModel::getTable(),
                'calendar'   => CalendarFeedModel::getTable(),
                'faq'        => FaqModel::getTable(),
                'user'       => UserModel::getTable(),
                default      => null,
            },
        };
    }

    /**
     * Parse the insert tag from a string.
     *
     * @Todo Handle query-Parameter (e.g. for file, picture, image,...)
     */
    private function parse(string $insertTag): void
    {
        [$command, $valueFlags] = explode("::", $insertTag, 2);

        $this->command = $command;

        if($valueFlags)
        {
            // Check for nested insert tags
            if(str_starts_with($valueFlags, '{{'))
            {
                // Extract flags of the main insert tag
                if(($flagPos = strrpos($valueFlags, '}}|')) !== false)
                {
                    // Get main flags
                    $flags = substr($valueFlags, $flagPos + 3);

                    // Remove main flags from insert tag string
                    $valueFlags = str_replace('|' . $flags, '', $valueFlags);

                    // Set main flags
                    $this->flags = explode('|', $flags);
                }

                $subInsertTag = new self();
                $subInsertTag->fromString($valueFlags);

                $this->value = $subInsertTag;

                return;
            }

            $valueFlags = explode("|", $valueFlags);

            $this->value = array_shift($valueFlags);
            $this->flags = $valueFlags;
        }
    }
}
