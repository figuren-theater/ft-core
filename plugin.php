<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols // impossible to do it another way
/**
 * Plugin Name:     figuren.theater | Core
 * Plugin URI:      https://github.com/figuren-theater/ft-core
 * Description:     Core components for a WordPress multisite plattform like figuren.theater
 * Author:          figuren.theater
 * Author URI:      https://figuren.theater
 * Text Domain:     figurentheater
 * Domain Path:     /languages
 * Version:         1.2.12
 *
 * @package         Figuren_Theater
 */

namespace Figuren_Theater;

use add_action;

use add_filter;
use Altis;
use do_action;

use FT_ROOT_DIR;

const DIRECTORY = __DIR__;

// This is usually done in altis/wp-config.php and needed in 'figuren-theater/altis-core'.
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
add_filter( 'plugins_url', __NAMESPACE__ . '\\fix_plugins_url', 1000, 3 );

// Fire module init hook and load enabled modules.
add_action(
	'altis.loaded_autoloader',
	function () {
		/**
		 * Modules should register themselves on this hook.
		 */
		do_action( 'altis.modules.init' );

		// Load modules.
		Altis\load_enabled_modules();
	},
	0
);

// Register core module.
add_action(
	'altis.modules.init',
	function () {
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
	}
);

