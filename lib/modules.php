<?php

namespace Kirby\Modules;

// Kirby dependencies
use C;
use Dir;
use Error;
use Str;
use Tpl;

/**
 * Modules
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class Modules {
	protected $page;
	
	// Caches
	protected $modules;
	protected static $allModules;
	
	/**
	 * Class constructor
	 *
	 * @param Page $page Kirby page that contains modules
	 */
	public function __construct($page) {
		$this->page = $page;
	}
	
	/**
	 * Outputs all modules of the given page
	 *
	 * @param  array   $data   Optional additional data to pass to each module
	 * @param  boolean $return Whether to output or return the module string
	 * @return string
	 */
	public function output($data = array(), $return = false) {
		$result = '';
		
		// Loop through all valid modules in the file system order
		foreach($this->modules() as $module) {
			$moduleObj  = new Module($module);
			$moduleName = $moduleObj->name();
			
			// Use the additional data but make sure that $module and $moduleName always win
			$moduleData = array_merge($data, compact('module', 'moduleName'));
			$result .= tpl::load($moduleObj->snippetFile(), $moduleData, true);
		}
		
		if($return) return $result;
		echo $result;
	}
	
	/**
	 * Returns a collection of module subpages of the given page
	 *
	 * @return Pages
	 */
	public function modules() {
		// Return from cache if possible
		if($this->modules) return $this->modules;
		
		// Determine where the modules live
		if($childPage = $this->page->find(static::parentUid())) {
			// Modules child page exists, use its children
			$modules = $childPage->children();
		} else {
			// Try to use the direct subpages (filtered below)
			$modules = $this->page->children();
		}
		
		// Filter the modules by visibility and valid module
		$modules = $modules->visible()->filter(function($page) {
			try {
				$module = new Module($page);
				return $module->validate();
			} catch(Error $e) {
				return false;
			}
		});
		
		return $this->modules = $modules;
	}
	
	/**
	 * Registers the page methods, page models and all blueprints within Kirby
	 * Called only once when the plugin is loaded
	 */
	public static function register() {
		$kirby = kirby();
		
		// Register $page->modules($data, $return) method
		// Calling it will call the modules() helper
		$kirby->set('page::method', 'modules', 'modules');
		
		// Register $page->moduleList() method
		$kirby->set('page::method', 'moduleList', function($page) {
			$modules = new static($page);
			return $modules->modules();
		});
		
		// Register $page->moduleCount($module) method
		$kirby->set('page::method', 'moduleCount', function($page, $module = null) {
			$moduleList = $page->moduleList();
			if($module) {
				$module = new Module($module);
				$moduleList = $moduleList->filterBy('intendedTemplate', $module->template());
			}
			
			return $moduleList->count();
		});
		
		// Register $page->hasModules($module) method
		$kirby->set('page::method', 'hasModules', function($page, $module = null) {
			return $page->moduleCount($module) > 0;
		});
		
		// Register blueprints, page models and dummy templates for all modules
		foreach(static::allModules() as $module) {
			$kirby->set('blueprint',   $module->template(), $module->blueprintFile());
			$kirby->set('page::model', $module->template(), 'kirby\\modules\\modulepage');
			$kirby->set('template',    $module->template(), dirname(__DIR__) . DS . 'etc' . DS . 'template.php');
		}
	}
	
	/**
	 * Returns the base directory for the modules
	 * Can be changed with the modules.directory option
	 *
	 * @return string
	 */
	public static function directory() {
		return c::get('modules.directory', kirby()->roots()->site() . DS . 'modules');
	}
	
	/**
	 * Returns the UID of the modules pages
	 * Can be changed with the modules.parent.uid option
	 *
	 * @return string
	 */
	public static function parentUid() {
		return c::get('modules.parent.uid', 'modules');
	}
	
	/**
	 * Returns the template prefix for modules
	 * Can be changed with the modules.template.prefix option
	 *
	 * @return string
	 */
	public static function templatePrefix() {
		return c::get('modules.template.prefix', 'module.');
	}
	
	/**
	 * Returns an array of all Module objects
	 *
	 * @return array
	 */
	public static function allModules() {
		// Return from cache if possible
		if(static::$allModules) return static::$allModules;
		$allModules = array();
		
		// Read the modules directory
		$basePath = static::directory();
		foreach(dir::read($basePath) as $name) {
			$path = $basePath . DS . $name;
			if(!is_dir($path)) continue;
			
			$module = new Module($name);
			if($module->validate()) $allModules[$name] = $module;
		}
		
		return static::$allModules = $allModules;
	}
}
