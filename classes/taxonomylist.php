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
     * Get taxonomy list.
     *
     * @return array
     */
    public function get()
    {
        if (!$this->taxonomylist) {
            $this->build();
        }
        return $this->taxonomylist;
    }

    /**
     * @internal
     */
    protected function build()
    {
        $taxonomylist = self::getGrav()['taxonomy']->taxonomy();
        $cache = self::getGrav()['cache'];
        $hash = hash('md5', serialize($taxonomylist));

        if ($taxonomy = $cache->fetch($hash)) {
            $this->taxonomylist = $taxonomy;
        } else {
            $newlist = [];
            foreach ($taxonomylist as $x => $y) {
                $partial = [];
                foreach ($taxonomylist[$x] as $key => $value) {
                    $taxonomylist[$x][strval($key)] = count($value);
                    $partial[strval($key)] = count($value);
                }
                arsort($partial);
                $newlist[$x] = $partial;
            }
            $cache->save($hash, $newlist);
            $this->taxonomylist = $newlist;
        }
    }
}
