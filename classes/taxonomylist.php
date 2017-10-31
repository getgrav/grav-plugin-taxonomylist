<?php

namespace Grav\Plugin;

use Grav\Common\GravTrait;

class Taxonomylist
{
    use GravTrait;

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
            $this->taxonomylist = $this->build(self::getGrav()['taxonomy']->taxonomy());
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
        $current = self::getGrav()['page'];
        $taxonomies = [];
        foreach ($current->children() as $child) {
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
        $cache = self::getGrav()['cache'];
        $hash = hash('md5', serialize($taxonomylist));
        $list = [];

        if ($taxonomy = $cache->fetch($hash . 'x')) {
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
