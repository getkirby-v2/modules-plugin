# Kirby Modules Plugin

With Kirby Modules you can make the content of your Kirby site modular. It allows users to create custom editorial pages that are composed of multiple content modules such as body text, galleries, pull quotes or maps.

The Modules plugin works great together with the [Patterns plugin](https://github.com/getkirby-plugins/patterns-plugin), see below.

## What is a module?

A module in the context of this plugin is:

1. A blueprint and a PHP snippet together with other optional files and assets that define the module's code.
2. A subpage in the content directory that uses the module's blueprint.

One content page can have multiple module subpages of different types and module subpages can contain images or other files depending on the module type.
These module subpages can be reordered in the Panel like other pages. The order in the Panel defines the order on the page. They can also be set to "invisible", which hides them on the page as well, so they can be used as drafts within a page.

You can find a demo of how such a module looks like [on the `demo` branch](https://github.com/getkirby-plugins/modules-plugin/tree/demo).

## Installation

To install the plugin, please put it in the `site/plugins` directory. Create a `site/modules` directory for the code of your modules.

### Creating modules

Each module has its own directory in `site/modules`, like `site/modules/text` or `site/modules/gallery`. Each module's directory looks like this:

```text
site/modules/
	gallery/
		gallery.html.php
		gallery.yml
		
		# Optional additional files, the names do not matter
		gallery.css
		gallery.js
		arrow.svg
		...
```

As you can see, only a snippet and a blueprint are required for a module to work.

Module blueprints work just like any other blueprint. They can allow or disallow files, you can set their title however you like and they can have as many fields as you need.

The snippet is a normal PHP file with the HTML and PHP code that defines your module. The snippet has access to the following variables:

- `$page` is the page on which the module appears
- `$module` is the module subpage, which you can use to access the fields from your module blueprint as well as module files
- `$moduleName` is the name of the module such as `text` or `gallery`
- and every other template variable like `$pages` and `$site`; you can also pass custom variables to the modules, see below

### Creating modular content in the Panel

To make use of modules, you need to add the module templates to your content blueprints:

**`site/blueprints/default.yml`**

```yaml
title: Default
pages:
  template:
    - module.text
    - module.gallery
...
```

Module templates have an automatic prefix of `module.`. You can change this prefix (see below), but please note that you will need to rename the content files of your module subpages after changing it.

**However** it is recommended to put the module subpages into their own directory instead of putting them directly into the directory of their parent page. The default name (UID) for this directory is `modules`, you can also change this (see below). To use this approach, you instead need the following blueprints:

**`site/blueprints/default.yml`**

```yaml
title: Default
pages:
  template: default
  build:
    - title: _modules
      uid: modules
      template: modules
...
```

**`site/blueprints/modules.yml`**

```yaml
title: Modules
pages:
  template:
    - module.text
    - module.gallery
```

Every time you then create a page with the default blueprint, the Panel will automatically create a modules directory and users will be able to create and organize their modules there.

### Printing the modules in the template

The plugin provides a `$page->modules()` method that automatically fetches all visible module pages for the given page, runs their snippets and outputs the resulting HTML.

Depending on your site, your default template can be as simple as this:

```php
<?php snippet('header') ?>
<?php $page->modules() ?>
<?php snippet('footer') ?>
```

There is also a `modules($page)` helper that works exactly the same.

#### Optional parameters

```php
$page->modules($data = array(), $return = false);
modules($page, $data = array(), $return = false);
```

The modules function and method behave like the `snippet()` function. `$data` can be used to provide additional data to all modules and `$return` can be set to `true` to return the HTML code instead of printing it.

## Configuration

If you follow the instructions above, you don't need to configure anything manually.
However there are the following options in case you need to change the defaults:

```php
// Base directory for the module code
c::set('modules.directory', '/var/www/yoursite.com/site/modules');

// UID to use when looking for the modules directory of a content page
c::set('modules.parent.uid', 'modules');

// Template prefix for module subpages (default results in "module.text")
c::set('modules.template.prefix', 'module.');
```

## Helper methods

Besides the main `$page->modules()` method that is used to output the module snippets, there are also a few other helper methods you can use:

### `$page->moduleList()`

Returns an array of the module pages for the given page.

### `$page->moduleCount($type)`

Returns the number of modules. If `$type` is given, returns the number of modules of that type.

### `$page->hasModules($type)`

Returns whether the page has any modules. If `$type` is given, returns whether the page has modules of that type.

### `$module->page()`

Returns the page where the module appears. Depending on your setup, it's either the parent page or the grandparent page.

### `$module->module()`

Returns the module object. You can use it to get more information about the module:

```php
var_dump($module->module()->name()); // Name of the module
var_dump($module->module()->template()); // Template name of the module
```

There are also a few other values of the module object, see `lib/module.php`.

## The module registry

You can also register modules from other plugins with the module [registry](https://getkirby.com/docs/developer-guide/plugins/registry):

```php
<?php

// Make sure that the Modules plugin is loaded
$kirby->plugin('modules');

// Register your module
$kirby->set('module', 'text', __DIR__ . DS . 'modules' . DS . 'text');
```

Like in the example above, you need to load the Modules plugin before registering your modules.

## Using together with the Patterns plugin

Since the plugin only requires the modules to have a snippet and a blueprint, modules can be stored inside the `site/patterns` directory if you use the [Patterns plugin](https://github.com/getkirby-plugins/patterns-plugin). This is useful if you want to present the different modules in the Patterns interface.

There are three things you need to watch out for:

1. Change the `modules.directory` option to `kirby()->roots()->site() . DS . 'patterns' . DS . 'modules'` so that the base path of the modules is inside the patterns directory
2. Move the modules to that directory if you haven't already
3. Make sure to create a `.config.php` file for each module that defines the `$module` variable so that the preview works:

```php
<?php

// Option 1: Return a random module of the respective type
return array(
	'preview' => function() {
		return array(
			'module' => site()->index()->filterBy('intendedTemplate', 'module.text')->shuffle()->first()
		);
	}
);

// OR
// Option 2: Return a custom module just for the preview
return array(
	'preview' => function() {
		// Define each field from your module blueprint with a value
		$data = array(
			'text' => 'Lorem ipsum dolor sit amet.'
		);
		
		// Return an Obj, which is pretty close to a page in its API
		return array(
			'module' => new Obj($data)
		);
	}
);
```

## License

<http://www.opensource.org/licenses/mit-license.php>

## Authors

Lukas Bestle & Sascha Lack <https://getkirby.com>
