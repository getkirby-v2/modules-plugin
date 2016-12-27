<?php

namespace Kirby\Modules;

/**
 * Renderer
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class Renderer {
	protected $page;
	
	// Cache
	protected $modulePages;
	
	/**
	 * Class constructor
	 *
	 * @param Page $page Kirby page that contains modules
	 */
	public function __construct($page) {
		$this->page = $page;
	}
	
	/**
	 * Renders all modules of the given page
	 *
	 * @param  array   $data   Optional additional data to pass to each module
	 * @param  boolean $return Whether to output or return the module string
	 * @return string
	 */
	public function render($data = [], $return = false) {
		$result = '';
		
		// Loop through all valid modules in the file system order
		foreach($this->modulePages() as $module) {
			$moduleObj = $module->module();
			if(!$moduleObj) continue;
			
			$result .= $moduleObj->render($module, $data);
		}
		
		if($return) {
			return $result;
		} else {
			echo $result;
		}
	}
	
	/**
	 * Returns a collection of module subpages of the given page
	 *
	 * @return Pages
	 */
	public function modulePages() {
		// Return from cache if possible
		if($this->modulePages) return $this->modulePages;
		
		// Determine where the module pages live
		if($childPage = $this->page->find(Settings::parentUid())) {
			// Modules child page exists, use its children
			$modulePages = $childPage->children();
		} else {
			// Try to use the direct subpages (filtered below)
			$modulePages = $this->page->children();
		}
		
		// Filter the module pages by visibility and valid module
		$modulePages = $modulePages->visible()->filter(function($page) {
			return Modules::instance()->get($page) !== false;
		});
		
		return $this->modulePages = $modulePages;
	}
}
