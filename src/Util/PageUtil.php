<?php

namespace Oveleon\ProductInstaller\Util;

use Contao\ArticleModel;
use Contao\PageModel;
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
 * @method array|null      getPagesSelectable(bool $disableRoot)
 * @method array|null      getArticleSelectable()
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class PageUtil
{
    static private ?Collection $articleCollection = null;
    static private ?Collection $pageCollection    = null;

    static private array $pagesStructured  = [];
    static private array $pagesFlat        = [];
    static private array $articlesFlat     = [];

    /**
     * Initialize PageUtil with a new collection of pages.
     * If no pages have been passed, the entire page structure is loaded or fetched from the cache.
     */
    public function setPages(?Collection $pageModelCollection = null, bool $useCache = true): self
    {
        // Skip if we already have a page collection, and it is not a manually set page collection
        if(null === $pageModelCollection)
        {
            if(null !== self::$pageCollection && $useCache)
            {
                return $this;
            }

            // Load all pages
            $pageModelCollection = PageModel::findAll(['order' => 'id ASC, sorting ASC']);
        }

        self::$pageCollection = $pageModelCollection;
        self::$pagesFlat      = [];

        // Break if collection has no entries
        if(!$pageModelCollection)
        {
            return $this;
        }

        $pages = array_combine(
            $pageModelCollection->fetchEach('id'),
            $pageModelCollection->fetchAll()
        );

        $nested = [];

        foreach ($pages as &$s)
        {
            if (!$pid = $s['pid'])
            {
                $s['title'] = html_entity_decode($s['title']);

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

                    $s['title'] = html_entity_decode($s['title']);

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
     * If no articles have been passed, the entire article structure is loaded or fetched from the cache.
     */
    public function setArticles(?Collection $articleModelCollection = null, bool $useCache = true): self
    {
        // Skip if we already have an article collection, and it is not a manually set article collection
        if(null === $articleModelCollection)
        {
            if(null !== self::$articleCollection && $useCache)
            {
                return $this;
            }

            // Load all articles
            $articleModelCollection = ArticleModel::findAll();
        }

        self::$articleCollection = $articleModelCollection;

        $pages = self::getPagesFlat();

        if(!$articleModelCollection || !count($pages))
        {
            return $this;
        }

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
                $article['title'] = html_entity_decode($article['title']);

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
            return [];
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

            case 'getPagesSelectable':
                $options = [];
                $disableRoot = $arguments[0] ?? false;

                foreach (self::getPagesFlat() as $page)
                {
                    $options[] = [
                        'value'     => $page['id'],
                        'text'      => $page['title'],
                        'class'     => $page['type'],
                        'info'      => $page['type'] === 'root' && $disableRoot ? '' : $page['id'],
                        'group'     => 'page',
                        'level'     => $page['_level'],
                        'disabled'  => $disableRoot && ($page['type'] === 'root'),
                    ];
                }

                return $options;

            case 'getArticleSelectable':
                $options = [];

                foreach (self::getArticlesFlat() as $pageArticles)
                {
                    $options[] = [
                        'value'     => ($pageArticles['_isArticle'] ?? false) ? $pageArticles['id'] : 'page_' . $pageArticles['id'],
                        'text'      => $pageArticles['title'],
                        'class'     => ($pageArticles['_isArticle'] ?? false) ? ($pageArticles['published'] ? 'article' : 'article_inv') : $pageArticles['type'],
                        'info'      => ($pageArticles['_isArticle'] ?? false) ? $pageArticles['id'] : '',
                        'group'     => 'page',
                        'level'     => $pageArticles['_level'],
                        'disabled'  => !($pageArticles['_isArticle'] ?? false),
                    ];
                }

                return $options;
        }

        return [];
    }

    /**
     * Array splice with preserving keys.
     *
     * @author  Lode <https://stackoverflow.com/users/230422/lode>
     * @link    https://stackoverflow.com/questions/16585502/array-splice-preserving-keys
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
