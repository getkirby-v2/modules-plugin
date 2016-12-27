<?php

/**
 * Kirby Modules Plugin
 * 
 * @author Lukas Bestle <lukas@getkirby.com>
 */

// Define autoloader
load([
	'kirby\\modules\\modules'    => __DIR__ . DS . 'lib' . DS . 'modules.php',
	'kirby\\modules\\module'     => __DIR__ . DS . 'lib' . DS . 'module.php',
	'kirby\\modules\\modulepage' => __DIR__ . DS . 'lib' . DS . 'modulepage.php',
	'kirby\\registry\\module'    => __DIR__ . DS . 'lib' . DS . 'moduleregistry.php',
	'kirby\\modules\\renderer'   => __DIR__ . DS . 'lib' . DS . 'renderer.php',
	'kirby\\modules\\settings'   => __DIR__ . DS . 'lib' . DS . 'settings.php'
]);

// Register page methods, blueprints and page models
$modules = Kirby\Modules\Modules::instance();
$modules->register();

/**
 * Helper function to render modules for a given page
 * You can also use $page->modules($data, $return)
 *
 * @param  Page    $page   Kirby page that contains modules
 * @param  array   $data   Optional additional data to pass to each module
 * @param  boolean $return Whether to output or return the module string
 * @return string
 */
function modules($page, $data = [], $return = false) {
	$modules = new Kirby\Modules\Renderer($page);
	return $modules->render($data, $return);
}
