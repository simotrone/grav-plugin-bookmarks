# Bookmarks Plugin

The **Bookmarks** Plugin is an extension for
[Grav CMS](http://github.com/getgrav/grav).
Manage Bookmarks from yaml file(s) to page view

## Installation

Installing the Bookmarks plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

I'm not on GPM, at the moment.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and
unzip it under `/your/site/grav/user/plugins`. Then rename the folder to
`bookmarks`.
You can find these files on
[GitHub](https://github.com/simotrone/grav-plugin-bookmarks) or via
[GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/bookmarks
	
> NOTE: This plugin is a modular component for Grav which may require other
> plugins to operate, please see its
> [blueprints.yaml-file on GitHub](https://github.com/simotrone/grav-plugin-bookmarks/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/bookmarks/bookmarks.yaml` to `user/config/plugins/bookmarks.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true

# errors in BookmarksList class break all (true) or continue (false)
fatal_error: true

# uri param name to select the item tag
# we can use 'and' or 'or' operator to filter by tag
filter:
    uri_param: bmtag
    operator: or

# if we want a tag-based subset we need to filter links by tag 
# with the starting_filter!
starting_filter:
    tag: []
    operator: or
# sorting key components for links item
sorting_key:
    - title
    - url

# Where are the file(s) to import?
imports:
    - 'user://data/bookmarks/file1.yaml'
    - 'user://data/bookmarks/file2.yaml'
```

!! Note: all the params are overwritable in page headers under `bookmarks`.

The following example filter the general bookmarks data set for a specific
page.
```yaml
---
title: my very personal bookmarks page
taxonomy:
    category: [ category, of, bookmarks, page ]
    tag: [ tag, of, bookmarks, page ]
cache_enable: false
bookmarks:
    starting_filter:
        tag: [ my, specific, tag ]
---
```

Note that if you use the Admin Plugin, a file with your configuration named bookmarks.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

Write your links in YAML file following this scheme:
```yaml
- url: *mandatory
  title: an url title
  description: the url description
  tag: [ wow, awesome, url ]
```

put them in a file as `user/data/bookmarks/astro.yaml` and insert the filepath
in `imports` directive!

Example:

```
# bookmarks skel
# - url*
#   title
#   description
#   tag []
- title: Duck Duck Go
  url: http://www.duckduckgo.com
  description: My favourite search engine
  tag: [ search engine, ducks ]
- url: https://www.bbc.com/
  tag: [ news ]
- title: Cherenkov Telescope Array Observatory
  url: https://www.cta-observatory.org
  tag: [ astronomy, cta ]
  description: The best telescope in the world! :-)
```

It is possibile to provide the css class for bookmarks tag in theme
configuration.
In the following example the style follows the spectre.css from quark.
```yaml
bookmarks-tags-class: label label-rounded label-secondary
```

## Credits

My job, my faults.

Surely thanks to all the grav plugin creators around for inspiration.

Thanks to [import plugin](https://github.com/Deester4x4jr/grav-plugin-import)
showed me I could.

## To Do

- [ ] We'll find.

