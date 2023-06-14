<?php

namespace Oveleon\ProductInstaller\Util;

use Model\Collection;

class PageUtil
{
    static private ?Collection $pageCollection = null;

    static private array $structured = [];
    static private array $flat = [];

    /**
     * Initialize PageUtil with a new collection of pages / set page collection.
     */
    public function set(Collection $pageModelCollection): self
    {
        self::$pageCollection = $pageModelCollection;
        self::$flat = [];

        $pages = array_combine(
            $pageModelCollection->fetchEach('id'),
            $pageModelCollection->fetchAll()
        );

        $nested = [];

        foreach ($pages as &$s)
        {
            if (!$pid = $s['pid'])
            {
                $nested[ $s['id'] ] = &$s;
            }
            else
            {
                if (isset($pages[$pid]))
                {
                    if (!isset($pages[$pid]['_children']))
                    {
                        $pages[$pid]['_children'] = [];
                    }

                    $pages[$pid]['_children'][ $s['id'] ] = &$s;
                }
            }
        }

        $flat = [];
        $level = 0;

        $flatten = function (&$page) use(&$flat, &$level, &$flatten)
        {
            if(!$page['pid'])
            {
                $level = 0;
            }

            $page['_level'] = $level;
            $flat[ $page['id'] ] = $page;


            if($children = ($page['_children'] ?? false))
            {
                $oldLevel = $level;
                ++$level;

                foreach ($children as &$subpage)
                {
                    $flatten($subpage);
                }

                $level = $oldLevel;
            }
        };

        foreach ($nested as $item)
        {
            $flatten($item);
        }

        self::$structured = $nested;
        self::$flat = $flat;

        return $this;
    }

    /**
     * Returns the full page model collection.
     */
    public function getCollection(): ?Collection
    {
        return self::$pageCollection;
    }

    /**
     * Returns the level of a page by id.
     */
    public function getLevel(int $pageId): int
    {
        return self::$flat[ $pageId ]['_level'] ?? 0;
    }

    /**
     * Returns the sorting of a page by id.
     */
    public function getSorting(int $pageId): int
    {
        return array_search($pageId, array_keys(self::$flat)) ?: 0;
    }

    /**
     * Returns the number of children of a page by id.
     */
    public function getNumberOfChildren(int $pageId): int
    {
        return count(self::$flat[ $pageId ]['_children'] ?? []);
    }

    /**
     * Returns an array with all pages and their nested child pages.
     */
    public function getStructured(): array
    {
        return self::$structured;
    }

    public function getFlat(): array
    {
        return self::$flat;
    }
}
