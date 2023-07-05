<?php

namespace Oveleon\ProductInstaller\Util;

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
}
