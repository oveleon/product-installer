<?php

namespace Oveleon\ProductInstaller\Util;

use Model\Collection;

/**
 * Class with helper functions for working with pages and articles.
 *
 * @method Collection|null getPageCollection()
 * @method Collection|null getArticleCollection()
 * @method array|null      getPagesStructured()
 * @method array|null      getPagesFlat()
 * @method array|null      getArticlesFlat()
 * @method array|null      getPageLevel(int $pageId)
 * @method array|null      getPageSorting(int $pageId)
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class PageUtil
{
    static private ?Collection $articleCollection = null;
    static private ?Collection $pageCollection    = null;

    static private array $pagesStructured = [];
    static private array $pagesFlat       = [];
    static private array $articlesFlat    = [];

    /**
     * Initialize PageUtil with a new collection of pages.
     */
    public function setPages(Collection $pageModelCollection): self
    {
        self::$pageCollection = $pageModelCollection;
        self::$pagesFlat      = [];

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

        self::$pagesStructured = $nested;
        self::$pagesFlat       = $flat;

        return $this;
    }

    /**
     * Initialize PageUtil with a new collection of articles.
     */
    public function setArticles(Collection $articleModelCollection): self
    {
        self::$articleCollection = $articleModelCollection;

        $pages = self::getPagesFlat();

        $articles = array_reverse(array_combine(
            $articleModelCollection->fetchEach('id'),
            $articleModelCollection->fetchAll()
        ));

        foreach ($articles as $article)
        {
            if(\array_key_exists($article['pid'], $pages))
            {
                // Get page
                $page  = $pages[$article['pid']];

                // Determine index to push in
                $index = array_search($article['pid'], array_keys($pages));

                // Set article information
                $article['_level'] = ++$page['_level'];
                $article['_isArticle'] = true;

                // Push to pages array
                self::array_splice_preserve_keys( $pages, ++$index, 0, ['art_' . $article['id'] => $article]);
            }
        }

        self::$articlesFlat = $pages;

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        if(null === self::$pageCollection)
        {
            throw new \RuntimeException('There is no page collection available. Add pages before using this method.');
        }

        switch ($name)
        {
            case 'getPageCollection':
                return self::$pageCollection;

            case 'getArticleCollection':
                return self::$articleCollection;

            case 'getPagesStructured':
                return self::$pagesStructured;

            case 'getPagesFlat':
                return self::$pagesFlat;

            case 'getArticlesFlat':
                return self::$articlesFlat;

            case 'getPageLevel':
                return self::$pagesFlat[$arguments[0] ]['_level'] ?? 0;

            case 'getPageSorting':
                return array_search($arguments[0], array_keys(self::$pagesFlat)) ?: 0;
        }
    }

    /**
     * Array splice with preserving keys.
     *
     * @author Lode <https://stackoverflow.com/users/230422/lode>
     * @link https://stackoverflow.com/questions/16585502/array-splice-preserving-keys
     */
    public static function array_splice_preserve_keys(&$input, $offset, $length=null, $replacement=array()): ?array
    {
        if (empty($replacement))
        {
            return array_splice($input, $offset, $length);
        }

        $part_before  = array_slice($input, 0, $offset, $preserve_keys=true);
        $part_removed = array_slice($input, $offset, $length, $preserve_keys=true);
        $part_after   = array_slice($input, $offset+$length, null, $preserve_keys=true);

        $input = $part_before + $replacement + $part_after;

        return $part_removed;
    }
}
