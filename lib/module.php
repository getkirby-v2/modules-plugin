<?php

namespace Kirby\Modules;

// Kirby dependencies
use Error;
use Obj;
use Page;
use Str;

/**
 * Module
 * 
 * @package Kirby Modules Plugin
 * @author  Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 */
class Module extends Obj {
	public $name;
	public $path;
	public $template;
	public $blueprintFile;
	public $snippetFile;
	
	/**
	 * Class constructor
	 *
	 * @param string $name Name of the module
	 * @param string $path Path to the module directory
	 */
	public function __construct($name, $path) {
		$this->name     = $name;
		$this->path     = $path;
		$this->template = Modules::templatePrefix() . $name;
		
		// Store the file paths of the module
		$this->blueprintFile = $path . DS . $name . '.yml';
		$this->snippetFile   = $path . DS . $name . '.html.php';
	}
	
	/**
	 * Validates if the module is valid and active
	 *
	 * @return boolean
	 */
	public function validate() {
		return is_file($this->blueprintFile) && is_file($this->snippetFile);
	}
}
