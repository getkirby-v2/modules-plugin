<?php

namespace Kirby\Modules;

// Kirby dependencies
use C;

/**
 * Settings
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class Settings {
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
}
