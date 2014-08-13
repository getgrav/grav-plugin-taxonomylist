# Grav Taxonomy List Plugin

`Taxonomylist` is a [Grav](http://github.com/getgrav/grav) plugin that generates a list of linked tags collected throughout	the site. 

# Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `taxonomylist`.

You should now have all the plugin files under

	/your/site/grav/user/plugins/taxonomylist
	
>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) plugins, and a theme to be installed in order to operate.

# Usage

To use `taxonomylist` you need to set your pages header with a  taxonomy category and tag:

```
taxonomy:
    category: blog
    tag: [tag1, tag2]
```

> NOTE: If you want to see this plugin in action, have a look at our [Blog Site Skeleton](http://github.com/grav/grav-skeleton-blog-site/archive/master.zip)

# Config Defaults
```
route: '/blog'
```
