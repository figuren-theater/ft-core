<?php
/**
 * Plugin Name:     figuren.theater | Core
 * Plugin URI:      https://github.com/figuren-theater/ft-core
 * Description:     Core components for a WordPress multisite plattform like figuren.theater
 * Author:          figuren.theater
 * Author URI:      https://figuren.theater
 * Text Domain:     figurentheater
 * Domain Path:     /languages
 * Version:         1.1.5
 *
 * @package         Figuren_Theater
 */


namespace Figuren_Theater;

use FT_ROOT_DIR;

use Altis;
use function Altis\load_enabled_modules;
use function Altis\register_module;

use add_action;
use add_filter;
use do_action;

const DIRECTORY = __DIR__;

// This is usually done in altis/wp-config.php
// and needed in 'figuren-theater/altis-core'
define( 'Altis\ROOT_DIR', FT_ROOT_DIR );


/**
 * This is the replacement for 
 * https://github.com/figuren-theater/altis-core/blob/master/load.php
 * which is not loaded as usual.
 *
 * Here only ~50% of the humanmade-original are loaded.
 *
 * ------------------------------------------------------------------
 * 
 * Main entry point loader for the Core module.
 *
 * @package altis/core
 */


// Patch plugins URL for vendor directory.
// add_filter( 'plugins_url', 'Altis\\fix_plugins_url', 10, 3 );
add_filter( 'plugins_url', __NAMESPACE__ . '\\fix_plugins_url', 1000, 3 );

// Ensure WP_ENVIRONMENT_TYPE is set.
// add_action( 'altis.loaded_autoloader', 'Altis\\set_wp_environment_type', -10 );

// Fire module init hook and load enabled modules.
add_action( 'altis.loaded_autoloader', function () {
	/**
	 * Modules should register themselves on this hook.
	 */
	do_action( 'altis.modules.init' );

	// Load modules.
	Altis\load_enabled_modules();
}, 0 );

// Register core module.
add_action( 'altis.modules.init', function () {
	Altis\register_module(
		'core',
		DIRECTORY,
		'Core',
		[
			'defaults' => [
				'enabled' => true,
			],
		],
		__NAMESPACE__ . '\\bootstrap'
	);
} );

// Load config entry point.
// add_action( 'altis.loaded_autoloader', function () {
// 	if ( file_exists( ROOT_DIR . '/.config/load.php' ) ) {
// 		require_once ROOT_DIR . '/.config/load.php';
// 	}
// } );
