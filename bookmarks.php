<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

/**
 * Class BookmarksPlugin
 * @package Grav\Plugin
 */
class BookmarksPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => [
                // ['autoload', 100000], // TODO: Remove when plugin requires Grav >=1.7
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized() {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
        ]);
    }

    public function onTwigTemplatePaths() {
        $this->grav['twig']->twig_paths[] = __DIR__.'/templates';
    }

    public function onTwigSiteVariables() {
        $page = $this->grav['page'];
        if ($page->template() != 'bookmarks') {
            return;
        }

        // merge plugin config with page config
        $merged_config = $this->mergeConfig($page, $deep=true);
        if (!$merged_config->get('imports')) {
            return;
        }

        require_once __DIR__.'/classes/bookmarkslist.php';
        $bl = new BookmarksList($merged_config->toArray());

        $tags_filter = [];
        $twig = $this->grav['twig'];
        $uri = $twig->twig_vars['uri']; // the same of $this->grav['uri']

        // filter links by uri params
        $filter_operator = $merged_config->get('filter.operator', 'or');
        $uri_param = $merged_config->get('filter.uri_param', 'bmtag');
        if ($uri->param(rawurldecode($uri_param))) {
            $tags_filter = explode(',', $uri->param($uri_param));
            $this->grav['log']->debug($this->name." | filters: ".join(', ', $tags_filter));
        }

        $twig->twig_vars['bookmarks'] = [
            'tags' => $bl->tags,
            'links' => $bl->filterLinks($tags_filter, $filter_operator),
            'uri_param' => $uri_param,
        ];
    }
}
