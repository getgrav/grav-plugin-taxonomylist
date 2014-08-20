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
        $taxonomylist = self::$grav['taxonomy']->taxonomy();
        foreach ($taxonomylist as $x => $y) {
            foreach ($taxonomylist[$x] as $key => $value) {
                $taxonomylist[$x][$key] = count($value);
            }
            array_multisort($taxonomylist[$x], SORT_DESC, SORT_NUMERIC);
        }
        $this->taxonomylist = $taxonomylist;
    }
}
