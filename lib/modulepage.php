<?php

namespace Kirby\Modules;

// Kirby dependencies
use Page;

/**
 * ModulePage
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class ModulePage extends Page {
	/**
	 * Returns the page where the module appears
	 *
	 * @return Page
	 */
	public function page() {
		if($this->parent()->uid() === Settings::parentUid()) {
			return $this->parent()->parent();
		} else {
			return $this->parent();
		}
	}
	
	/**
	 * Returns the module object
	 *
	 * @return Module
	 */
	public function module() {
		return Modules::instance()->get($this);
	}
	
	/**
	 * Renders this single module
	 *
	 * @param  array   $data   Optional additional data to pass to the snippet
	 * @param  boolean $return Whether to output or return the module string
	 * @return string
	 */
	public function render($data = [], $return = false) {
		$result = $this->module()->render($this, $data);
		
		if($return) {
			return $result;
		} else {
			echo $result;
		}
	}
}
