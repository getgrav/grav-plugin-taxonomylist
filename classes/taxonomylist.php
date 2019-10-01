<?php

namespace Grav\Plugin;

use Grav\Common\Grav;

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
        if (!$this->taxonomylist) {
            $this->taxonomylist = $this->build(Grav::instance()['taxonomy']->taxonomy());
        }
        return $this->taxonomylist;
    }

    /**
     * Get taxonomy list with only tags of the child pages.
     * @param type $child_only // will recurs through all descedants if set to false
     * @param type $current // page to start with
     * @param array $taxonomies //array to feed with tags
     * @return array
     */
    public function getChildPagesTags( $child_only=true, $current=null , array &$taxonomies=[] )
    {
        if(is_null($current))
        {
            $current = Grav::instance()['page'];
        }
        foreach ( $current->children()->published() as $child) 
        {
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
           // recurse
            if(!$child_only) $this->getChildPagesTags($child_only, $child, $taxonomies);
        }
        array_multisort( $taxonomies );
        return $taxonomies;
    }

    /**
     * @internal
     * @param array $taxonomylist
     * @return array
     */
    protected function build(array $taxonomylist)
    {
        $cache = Grav::instance()['cache'];
        $hash = hash('md5', serialize($taxonomylist));
        $list = [];

        if ($taxonomy = $cache->fetch($hash)) {
            return $taxonomy;
        } else {
            foreach ($taxonomylist as $taxonomyName => $taxonomyValue) {
                $partial = [];
                foreach ($taxonomyValue as $key => $value) {
                    if (is_array($value)) {
                        $taxonomyValue[strval($key)] = count($value);
                        $partial[strval($key)] = count($value);
                    } else {
                        $partial[strval($value)] = 1;
                    }
                }
                arsort($partial);
                $list[$taxonomyName] = $partial;
            }

            $cache->save($hash, $list);

            return $list;
        }
    }
}
