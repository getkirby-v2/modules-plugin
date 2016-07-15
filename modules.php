<?php

/**
 * Kirby Modules Plugin
 * 
 * @author Lukas Bestle <lukas@getkirby.com>
 */

// Define autoloader
load(array(
	'kirby\\modules\\modules' => __DIR__ . DS . 'lib' . DS . 'modules.php',
	'kirby\\modules\\module'  => __DIR__ . DS . 'lib' . DS . 'module.php',
));

// Register page method and blueprints
Kirby\Modules\Modules::register();

/**
 * Helper function to output modules for a given page
 * You can also use $page->modules($data, $return)
 *
 * @param  Page    $page   Kirby page that contains modules
 * @param  array   $data   Optional additional data to pass to each module
 * @param  boolean $return Whether to output or return the module string
 * @return string
 */
function modules($page, $data = array(), $return = false) {
	$modules = new Kirby\Modules\Modules($page);
	return $modules->output($data, $return);
}
