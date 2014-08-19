<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\Taxonomylist;

class TaxonomylistPlugin extends Plugin
{

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onAfterTwigTemplatesPaths' => ['onAfterTwigTemplatesPaths', 0],
            'onAfterTwigSiteVars' => ['onAfterTwigSiteVars', 0]
        ];
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onAfterTwigTemplatesPaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Set needed variables to display the taxonomy list.
     */
    public function onAfterTwigSiteVars()
    {
        require_once __DIR__ . '/classes/taxonomylist.php';

        $twig = $this->grav['twig'];
        $twig->twig_vars['taxonomylist'] = new Taxonomylist();
        $twig->twig_vars['list_url'] = $this->config->get(
            'site.blog.route',
            $this->config->get('plugins.taxonomylist.route')
        );
    }
}
