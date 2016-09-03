<?php

namespace Kirby\Registry;

// Kirby and PHP dependencies
use Dir;
use Exception;

// Internal dependencies
use Kirby\Modules\Modules;

/**
 * Module registry
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class Module extends Entry {
	// Store of registered modules
	protected static $modules = [];
	
	/**
	 * Adds a new module to the registry
	 * 
	 * @param  string $name
	 * @param  string $path Valid module directory path
	 * @return string
	 */
	public function set($name, $path) {
		if(is_dir($path)) {
			// Register the new module
			static::$modules[$name] = $path;
			Modules::registerModule($name);
			
			return $path;
		} else if($this->kirby->option('debug')) {
			throw new Exception('The module does not exist at the specified path: ' . $path);
		}
	}
	
	/**
	 * Retreives a module from the registry
	 * If called without params, retrieves a list of module names
	 * 
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name = null) {
		if(is_null($name)) {
			$modules  = [];
			$basePath = Modules::directory();
			foreach(dir::read($basePath) as $module) {
				if(is_dir($basePath . DS . $module)) $modules[] = $module;
			}
			return array_unique(array_merge($modules, array_keys(static::$modules)));
		}
		
		// Get from main modules directory
		$path = Modules::directory() . DS . $name;
		if(is_dir($path)) return $path;
		
		// Get from registry
		if(isset(static::$modules[$name])) return static::$modules[$name];
		
		// No match
		return false;
	}
}
