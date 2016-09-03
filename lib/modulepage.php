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
		if($this->parent()->uid() === Modules::parentUid()) {
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
		return Modules::module($this);
	}
}
