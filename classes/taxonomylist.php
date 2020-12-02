<?php

namespace Grav\Plugin;

use Grav\Common\Cache;
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
     * @return array
     */
    public function get()
    {
        if (null === $this->taxonomylist) {
            $this->taxonomylist = $this->build(Grav::instance()['taxonomy']->taxonomy());
        }

        return $this->taxonomylist;
    }

    /**
     * Get taxonomy list with only tags of the child pages.
     *
     * @return array
     */
    public function getChildPagesTags()
    {
        /** @var PageInterface $current */
        $current = Grav::instance()['page'];
        $taxonomies = [];
        foreach ($current->children()->published() as $child) {
            if (!$child->isPage()) {
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
            }
        }

        return $taxonomies;
    }

    /**
     * @internal
     * @param array $taxonomylist
     * @return array
     */
    protected function build(array $taxonomylist)
    {
        /** @var Cache $cache */
        $cache = Grav::instance()['cache'];
        $hash = hash('md5', serialize($taxonomylist));
        $list = [];

        if ($taxonomy = $cache->fetch($hash)) {
            return $taxonomy;
        }

        foreach ($taxonomylist as $taxonomyName => $taxonomyValue) {
            $partial = [];
            foreach ($taxonomyValue as $key => $value) {
                if (is_array($value)) {
                    $key = (string)$key;
                    $taxonomyValue[$key] = count($value);
                    $partial[$key] = count($value);
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
}
