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
        $this->taxonomylist = $newlist;
    }
}
