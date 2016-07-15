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
	public $template;
	public $blueprintFile;
	public $snippetFile;
	
	/**
	 * Class constructor
	 *
	 * @param string $name Name of the module directory or module page
	 */
	public function __construct($name) {
		$templatePrefix = Modules::templatePrefix();
		
		// Get the module name from the template if a page is given
		if(is_a($name, 'Page')) {
			// Validate that the page is a module
			if(!str::startsWith($name->intendedTemplate(), $templatePrefix)) {
				throw new Error('The given page is no module.');
			}
			
			$prefixLength = str::length($templatePrefix);
			$name = str::substr($name->intendedTemplate(), $prefixLength);
		}
		
		$this->name     = $name;
		$this->template = $templatePrefix . $name;
		
		// Store the file paths of the module
		$basePath = Modules::directory() . DS . $name;
		$this->blueprintFile = $basePath . DS . $name . '.yml';
		$this->snippetFile   = $basePath . DS . $name . '.html.php';
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
