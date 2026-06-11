<?php

namespace Grav\Plugin;

use Grav\Common\Cache;
use Grav\Common\Data\Data;
use Grav\Common\Grav;
use Grav\Common\Page\Interfaces\PageInterface;

class Taxonomylist
{
    /**
     * @var array
     */
    protected $taxonomylist;

    /**
     * Get taxonomy list with all tags of the site.
     *
     * When the `respect_access` config option is enabled, taxonomy values are
     * counted only across pages the current visitor is authorized to see, and
     * values with no accessible pages are dropped from the list. This is opt-in
     * because it loads and authorizes every tagged page on the site, and the
     * resulting counts are per-visitor.
     *
     * @return array
     */
    public function get()
    {
        if (null === $this->taxonomylist) {
            $respect_access = (bool) Grav::instance()['config']->get('plugins.taxonomylist.respect_access', false);
            $this->taxonomylist = $this->build(Grav::instance()['taxonomy']->taxonomy(), $respect_access);
        }

        return $this->taxonomylist;
    }

    /**
     * Get taxonomy list with only tags of the child pages.
     *
     * @return array
     */
    public function getChildPagesTags(?PageInterface $page = null, bool $child_only = true, array $taxonomies= [])
    {
        /** @var PageInterface $page */
        if (null === $page) {
            $page = Grav::instance()['page'];
        }

        foreach ($page->children()->published() as $child) {
            if (!$child->isPage()) {
                continue;
            }
            // Respect access rules: skip child pages the current visitor isn't authorized to see.
            if (!$this->isAccessible($child)) {
                continue;
            }
            foreach($this->build($child->taxonomy()) as $taxonomyName => $taxonomyValue) {
                if (!isset($taxonomies[$taxonomyName])) {
                    $taxonomies[$taxonomyName] = $taxonomyValue;
                } else {
                    foreach ($taxonomyValue as $value => $count) {
                        if (!isset($taxonomies[$taxonomyName][$value])) {
                            $taxonomies[$taxonomyName][$value] = $count;
                        } else {
                            $taxonomies[$taxonomyName][$value] += $count;
                        }
                    }
                }
                if(!$child_only && $child->children()->count() > 0) {
                    $taxonomies = $this->getChildPagesTags($child, $child_only, $taxonomies);
                }
            }
        }
        array_multisort($taxonomies);

        return $taxonomies;
    }

    /**
     * Determine whether the current visitor is authorized to see a page.
     *
     * Grav core has no concept of `access` - it is enforced entirely by the Login
     * plugin via the PageAuthorizeEvent. We delegate to the same check Login uses
     * when dispatching a page, so taxonomy counts match what the visitor can
     * actually reach. When the Login plugin isn't installed there are no access
     * rules to honor, so the page is treated as accessible.
     *
     * @param PageInterface $page
     * @return bool
     */
    protected function isAccessible(PageInterface $page)
    {
        $grav = Grav::instance();
        $login = $grav['login'] ?? null;
        $user = $grav['user'] ?? null;

        if (null === $login || null === $user) {
            return true;
        }

        // Pass Login's config so options like `parent_acl` are honored.
        $config = new Data((array) $grav['config']->get('plugins.login'));

        return $login->isUserAuthorizedForPage($user, $page, $config);
    }

    /**
     * @internal
     * @param array $taxonomylist
     * @param bool  $respect_access When true, count only pages the visitor can access.
     * @return array
     */
    protected function build(array $taxonomylist, $respect_access = false)
    {
        /** @var Cache $cache */
        $cache = Grav::instance()['cache'];
        // Access-filtered counts are per-visitor, so fold the visitor's identity
        // into the cache key to avoid serving one user's counts to another.
        $signature = $respect_access ? '|access:' . $this->accessSignature() : '';
        $hash = hash('md5', serialize($taxonomylist) . $signature);
        $list = [];

        if ($taxonomy = $cache->fetch($hash)) {
            return $taxonomy;
        }

        foreach ($taxonomylist as $taxonomyName => $taxonomyValue) {
            $partial = [];
            foreach ($taxonomyValue as $key => $value) {
                if (is_array($value)) {
                    $key = (string)$key;
                    $count = $respect_access ? $this->countAccessible($value) : count($value);
                    // Drop taxonomy values that have no pages the visitor can see.
                    if ($count === 0) {
                        continue;
                    }
                    $partial[$key] = $count;
                } else {
                    $partial[(string)$value] = 1;
                }
            }
            arsort($partial);
            $list[$taxonomyName] = $partial;
        }

        $cache->save($hash, $list);

        return $list;
    }

    /**
     * Count how many of the given taxonomy-map pages the current visitor can access.
     *
     * The taxonomy map keys each entry by page path, so we resolve each path back
     * to its page and run the same access check Grav uses when serving it.
     *
     * @param array $pages Array keyed by page path (the taxonomy map value).
     * @return int
     */
    protected function countAccessible(array $pages)
    {
        $repository = Grav::instance()['pages'];
        $count = 0;
        foreach (array_keys($pages) as $path) {
            $page = $repository->get($path);
            if (null !== $page && $this->isAccessible($page)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Build a cache signature that uniquely identifies the current visitor's
     * access. Anonymous visitors share one signature; authenticated users are
     * keyed individually so their access-filtered counts don't leak.
     *
     * @return string
     */
    protected function accessSignature()
    {
        $user = Grav::instance()['user'] ?? null;
        if (null === $user || empty($user->authenticated)) {
            return 'guest';
        }

        return (string) ($user->username ?? 'user');
    }
}
