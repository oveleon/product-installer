<?php

namespace Oveleon\ProductInstaller\Util;

use Contao\ArticleModel;
use Contao\PageModel;
use Oveleon\ProductInstaller\Import\TableImport;
use Oveleon\ProductInstaller\Import\Validator\CollectionValidator;
use Oveleon\ProductInstaller\Import\Validator\ValidatorMode;
use Oveleon\ProductInstaller\InsertTag;

/**
 * Class with helper functions for working with insert tags.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class InsertTagUtil
{
    /**
     * Extract Insert Tags of a string and return an array of InsertTags
     *
     * @return null|array<InsertTag>
     */
    public static function extractInsertTags(string $buffer): ?array
    {
        $insertTags = null;

        foreach (self::matchInsertTags($buffer) ?? [] as $insertTag)
        {
            $tag = new InsertTag();
            $tag->fromString($insertTag);

            if($tag->getCommand())
            {
                $insertTags[] = $tag;
            }
        }

        return $insertTags;
    }

    /**
     * Check if an insert tag exists in a string.
     */
    public static function hasInsertTags(?string $buffer): bool
    {
        if(!$buffer)
        {
            return false;
        }

        return (bool) self::matchInsertTags($buffer);
    }

    /**
     * Match and returns all insert tags from a string.
     */
    public static function matchInsertTags(string $buffer): ?array
    {
        preg_match_all('/{{(?:[^{}]|(?R))*}}/', $buffer, $matches, PREG_PATTERN_ORDER);

        return $matches[0] ?? null;
    }


    /**
     * Helper method to detect and replace insert tags, generate prompt or add lifecycle validators if not connectable.
     */
    public static function detectInsertTagsAndReplace(array $subset, ?array &$notConnectable, array $row, string $model, TableImport $importer, ?array &$modifiedFields = null): array
    {
        foreach ($subset as $key => $value)
        {
            if(\is_array($value))
            {
                $subset[$key] = self::detectInsertTagsAndReplace($value, $notConnectable, $row, $model, $importer);
            }
            elseif(self::hasInsertTags($value))
            {
                if($insertTags = self::extractInsertTags($value))
                {
                    // Replace insert tag and add prompts to importer dynamically
                    $subset[$key] = self::replaceInsertTagsOrPrompt($value, $insertTags, $notConnectable, $row, $model, $importer);

                    // Add field to modified field collection
                    $modifiedFields[] = $key;
                }
            }
        }

        return $subset;
    }

    /**
     * Helper method to replace insert tags, generate prompt or add lifecycle validators if not connectable.
     */
    public static function replaceInsertTagsOrPrompt(string $string, array $insertTags, ?array &$notConnectable, array $row, string $model, TableImport $importer): string
    {
        foreach ($insertTags as $insertTag)
        {
            /** @var InsertTag $insertTag */
            $value = $insertTag->getValue();

            // Check for nested insert tags
            if($value instanceof InsertTag)
            {
                return self::replaceInsertTagsOrPrompt($string, [$insertTag->getValue()], $notConnectable, $row, $model, $importer);
            }

            $search  = $insertTag->toString();
            $replace = null;
            $table   = $insertTag->getRelatedTable();

            switch ($table)
            {
                case ArticleModel::getTable():
                case PageModel::getTable():

                    if(
                        ($command = $insertTag->getCommand(true)) === 'link' ||         // 1. Handle pages
                        ($command = $insertTag->getCommand())               === 'insert_article'  // 2. Handle article includes
                    )
                    {
                        // Check existing connections
                        if($connectedId = $importer->getConnection($value, $table))
                        {
                            // Overwrite the insert tag value with the connected id
                            $insertTag->setValue($connectedId);

                            // Set replace with the new insert tag string
                            $replace = $insertTag->toString();
                        }
                        // Set non-connectable or create lifecycle validator
                        else
                        {
                            // If the table is still imported, we will try to connect later
                            if($importer->willBeImported($table))
                            {
                                // Add insert tag connection to retrieve them in the new validator (searchId, pageId). See method `connectInsertTag` for more information.
                                $importer->addConnection($value, json_encode(['id' => $row['id'], 'modelClass' => $model]), '_connectInsertTag');

                                // Add persist layout validator
                                $importer->addLifecycleValidator('connectInsertTag_' . $value, $table, [CollectionValidator::class, 'connectInsertTag'], ValidatorMode::AFTER_IMPORT);
                            }
                            // Otherwise we need a prompt
                            else
                            {
                                $notConnectable[] = [
                                    'table'   => $table,
                                    'value'   => $value,
                                    'command' => $command
                                ];
                            }
                        }
                    }

                    break;

                // ToDo: Add more cases
            }

            if(null !== $replace)
            {
                $string = str_replace($search, $replace, $string);
            }
        }

        return $string;
    }

}
