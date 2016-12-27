<?php

namespace Kirby\Modules;

// Kirby dependencies
use Collection;
use Str;

/**
 * Modules
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class Modules extends Collection {
	public static $instance;
	
	/**
	 * Returns the singleton class instance
	 *
	 * @return Modules
	 */
	public static function instance() {
		if(!is_null(static::$instance)) return static::$instance;
		return static::$instance = new static();
	}
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		$modules = [];
		
		// Get the module names from the registry
		foreach(kirby()->get('module') as $name) {
			$module = static::module($name);
			if($module) $modules[$name] = $module;
		}
		
		$this->data = $modules;
	}
	
	/**
	 * Registers the page methods, page models and all blueprints within Kirby
	 * Called only once when the plugin is loaded
	 */
	public function register() {
		$kirby = kirby();
		
		// Register $page->modules($data, $return) method
		// Calling it will call the modules() helper
		$kirby->set('page::method', 'modules', 'modules');
		
		// Register $page->moduleList() method
		$kirby->set('page::method', 'moduleList', function($page) {
			$modules = new Renderer($page);
			return $modules->modulePages();
		});
		
		// Register $page->moduleCount($module) method
		$kirby->set('page::method', 'moduleCount', function($page, $module = null) {
			$moduleList = $page->moduleList();
			if($module) {
				$module = $this->get($module);
				if(!$module) return 0;
				$moduleList = $moduleList->filterBy('intendedTemplate', $module->template());
			}
			
			return $moduleList->count();
		});
		
		// Register $page->hasModules($module) method
		$kirby->set('page::method', 'hasModules', function($page, $module = null) {
			return $page->moduleCount($module) > 0;
		});
		
		// Register $page->isModule() method
		$kirby->set('page::method', 'isModule', function($page) {
			return is_a($page, 'Kirby\\Modules\\ModulePage');
		});
		
		// Register blueprints, page models and dummy templates for all modules
		foreach($this as $module) {
			$module->register();
		}
	}
	
	/**
	 * Called after a new module has been registered
	 *
	 * @param string $name Module name
	 */
	public function registryNotification($name) {
		$module = static::module($name);
		if($module) {
			// Update the collection data
			$this->data[$name] = $module;
			
			// Register module
			$module->register();
		}
	}
	
	/**
	 * Returns a module object by name or page
	 *
	 * @param  mixed  $key Module name or module page
	 * @return Module
	 */
	public function get($key, $default = null) {
		// Get the module name from the template if a page is given
		if(is_a($key, 'Page')) {
			$templatePrefix = Settings::templatePrefix();
			
			// Validate that the page is a module
			if(!str::startsWith($key->intendedTemplate(), $templatePrefix)) return null;
			
			$prefixLength = str::length($templatePrefix);
			$key = str::substr($key->intendedTemplate(), $prefixLength);
		}
		
		return parent::get($key, $default);
	}
	
	/**
	 * Makes a module object by name
	 *
	 * @param  string $name Module name
	 * @return Module
	 */
	protected static function module($name) {
		$path = kirby()->get('module', $name);
		if(!$path) return false;
		
		$module = new Module($name, $path);
		if($module->validate()) {
			return $module;
		} else {
			return false;
		}
	}
}
