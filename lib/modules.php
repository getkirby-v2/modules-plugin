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
	protected static $moduleObjs;
	
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
			$moduleObj = static::module($module);
			if(!$moduleObj) continue;
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
				$module = static::module($page);
				return $module && $module->validate();
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
				$module = static::module($module);
				if(!$module) return 0;
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
			static::registerModule($module);
		}
	}
	
	/**
	 * Registers the blueprint, page model and template for a module
	 * 
	 * @param Module/string $module Module object or module name
	 */
	public static function registerModule($module) {
		$kirby = kirby();
		
		// Make sure that we have a module object
		if(is_string($module)) {
			$moduleName = $module;
			$module     = static::module($moduleName);
			if(!$module) throw new Error('Invalid module ' . $moduleName);
		}
		
		$kirby->set('blueprint',   $module->template(), $module->blueprintFile());
		$kirby->set('page::model', $module->template(), 'kirby\\modules\\modulepage');
		$kirby->set('template',    $module->template(), dirname(__DIR__) . DS . 'etc' . DS . 'template.php');
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
	 * Returns a module object by name or module page object
	 * If the module does not exists, returns false
	 *
	 * @param  string/Page $name Module name or module page
	 * @return Module
	 */
	public static function module($name) {
		// Get the module name from the template if a page is given
		if(is_a($name, 'Page')) {
			$templatePrefix = static::templatePrefix();
			
			// Validate that the page is a module
			if(!str::startsWith($name->intendedTemplate(), $templatePrefix)) {
				throw new Error('The given page is no module.');
			}
			
			$prefixLength = str::length($templatePrefix);
			$name = str::substr($name->intendedTemplate(), $prefixLength);
		}
		
		// Return from cache if possible
		if(isset(static::$moduleObjs[$name])) return static::$moduleObjs[$name];
		
		// Get the path of the module
		$path = kirby()->get('module', $name);
		if(!$path) return false;
		
		// Create a new module object and validate the module
		$module = new Module($name, $path);
		if($module->validate()) {
			return static::$moduleObjs[$name] = $module;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns an array of all valid Module objects
	 *
	 * @return array
	 */
	public static function allModules() {
		// Get the module names from the registry
		$allModules = array();
		foreach(kirby()->get('module') as $name) {
			$module = static::module($name);
			if($module) $allModules[$name] = $module;
		}
		
		return $allModules;
	}
}
