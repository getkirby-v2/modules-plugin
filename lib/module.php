<?php

namespace Kirby\Modules;

// Kirby dependencies
use Obj;
use Tpl;

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
		$this->template = Settings::templatePrefix() . $name;
		
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
	
	/**
	 * Registers the module
	 */
	public function register() {
		$kirby = kirby();
		
		$kirby->set('blueprint',   $this->template(), $this->blueprintFile());
		$kirby->set('page::model', $this->template(), 'kirby\\modules\\modulepage');
		$kirby->set('template',    $this->template(), dirname(__DIR__) . DS . 'etc' . DS . 'template.php');
	}
	
	/**
	 * Renders the module snippet with the given data
	 *
	 * @param  Page   $page Module page
	 * @param  array  $data Optional additional data to pass to the snippet
	 * @return string
	 */
	public function render($page, $data = []) {
		// Use the additional data but make sure that $module and $moduleName always win
		$data = array_merge($data, [
			'module'     => $page,
			'moduleName' => $this->name()
		]);
		
		return tpl::load($this->snippetFile(), $data, true);
	}
}
