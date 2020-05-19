<?php

namespace Grav\Plugin;
use Grav\Common\Grav;
use Symfony\Component\Yaml\Yaml;

class BookmarksList {
    public $links = [];
    public $tags = [];

    private $locator;
    private $imports = [];
    private $sorting_key_components = [];
    private $fatal_error;
    private $dispatchFilter = [
        'or' => 'filterLinksWithOr',
        'and' => 'filterLinksWithAnd',
    ];

    function __construct($config) {
        $this->imports = is_array($config['imports'])
                       ? $config['imports']
                       : [ $config['imports'] ];
        $this->fatal_error = $config['fatal_error'] ?? true;
        $this->sorting_key_components = $config['sorting_key'];

        $this->init();
        // import data from imports
        $this->importData();

        // add sorting_key to link and "no tag" tag where missing
        $this->augmentData();

        // filter starting data
        if (isset($config['starting_filter'])) {
            $tags_filter = $config['starting_filter']['tag'];
            $filter_operator = $config['starting_filter']['operator'] ?? 'or';
            if (count($tags_filter) > 0) {
                $this->links = $this->filterLinks($tags_filter, $filter_operator);
            }
        }

        // build tags map
        $this->buildTagsMap();
    }

    private function init() {
        $grav = Grav::instance();
        $this->locator = $grav['locator'];
    }

    private function importData($fatal_error=true) {
        foreach ($this->imports as $file_uri) {
            try {
                $filepath = $this->locator->findResource($file_uri);
                if (!$filepath) {
                    throw new \Exception("Resource '$file_uri' not found.");
                }
                $content = file_get_contents($filepath);
                $decoded = YAML::parse($content);
                $this->links = array_merge($this->links, $decoded);
            }
            catch (\Exception $e) {
                if ($this->fatal_error) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
    }

    /* add sorting_key to link.
     * add "no tag" tag to links have no one.
     */
    private function augmentData() {
        $n = count($this->sorting_key_components);

        foreach ($this->links as $i => $link) {
            if (!key_exists('tag', $link)) {
                $link['tag'] = ['no tag'];
            }

            if ($n > 0) {
                $link['sorting_key'] = '';
                foreach ($this->sorting_key_components as $f) {
                    if ($f == 'url') {
                        // cleanup url for sorting purpouse
                        $link['sorting_key'] .= preg_replace('/https?:\/\/(www\.)?/i', '', $link[$f]);
                    }
                    if ($f == 'tag') {
                        $tmp = array_map('strtolower', $link[$f]);
                        // dunno, we want them sorted? :/
                        // pro w/ sort: general consistency.
                        // pro w/o sort: if user put data tag in specific order,
                        //               we get "blocks" of links (maybe the
                        //               first tag is meaningful).
                        // sort($tmp);
                        $link['sorting_key'] .= implode('.', $tmp);
                    }
                    elseif (key_exists($f, $link)) {
                        $link['sorting_key'] .= strtolower($link[$f]);
                    }
                }
            }

            $this->links[$i] = $link;
        }
    }

    private function buildTagsMap() {
        $tags = [];
        foreach ($this->links as $link) {
            foreach ($link['tag'] as $tag) {
                if (!isset($tags[$tag])) {
                    $tags[$tag] = [
                        'count' => 0,
                        'name' => $tag,
                        'name_lc' => strtolower($tag),
                    ];
                }
                $tags[$tag]['count'] += 1;
            }
        }
        $this->tags = array_values($tags);
    }

    /* filter links with different operator
     * $filters = array(words, have, to, match, the, link, tag)
     * $operator = or|and
     */
    public function filterLinks($filters = array(), $operator = 'or') {
        $filter_func = $this->dispatchFilter[strtolower($operator)];
        return $this->$filter_func($filters);
    }

    private function filterLinksWithOr($filters = array()) {
        if (count($filters) < 1) {
            return $this->links;
        }
        $links = array_filter($this->links, function($link) use ($filters) {
            foreach ($filters as $word) {
                if (in_array($word, $link['tag'])) {
                    return true;
                }
            }
            return false;
        });
        return $links;
    }

    private function filterLinksWithAnd($filters = array()) {
        $filters_num = count($filters);
        if ($filters_num < 1) {
            return $this->links;
        }
        $links = array_filter($this->links, function($link) use ($filters, $filters_num) {
            $count = 0;
            foreach ($filters as $word) {
                if (in_array($word, $link['tag'])) {
                    $count++;
                }
            }
            return $count == $filters_num ? true : false;
        });
        return $links;
    }
}
