<?php
/**
 * This is the replacement for
 * https://github.com/figuren-theater/altis-core/blob/master/inc/namespace.php
 * which is not loaded as usual.
 *
 * ------------------------------------------------------------------
 *
 * Core platform functions.
 *
 * @package altis/core
 */

namespace Figuren_Theater;

use FT_VENDOR_DIR;

use WP_CONTENT_URL;

use Altis;
use function Altis\get_config as get_altis_config;

use function apply_filters;

/**
 * Bootstrap any core functions as necessary.
 *
 * @return void
 */
function bootstrap() :void {
	/*
	 * Maybe this could be re-enabled / re-used at some point.
	 *

	About\bootstrap();
	Upgrades\bootstrap();
	Telemetry\bootstrap();

	Global_Content\bootstrap();

	Register the Altis command.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::add_command( 'altis', __NAMESPACE__ . '\\Command' );
		Bind the migrate command to run after initial install.
		WP_CLI::add_hook( 'after_invoke:core multisite-install', function () {
			WP_CLI::runcommand( 'altis migrate' );
		} );
	}
	*/
}


/**
 * Fix the plugins_url for files in the vendor directory
 *
 * @param string $url The current plugin URL.
 * @param string $path The relative path to a file in the plugin folder.
 * @param string $plugin The absolute path to the plugin file.
 *
 * @return string
 */
function fix_plugins_url( string $url, string $path, string $plugin = null ) : string {
	$_original_url = $url;
	// \do_action( 'qm/debug', [ __FUNCTION__, $url, $path, $plugin ] );
	// the string to find
	// and replace with 'v' - our symlink to the root vendor folder
	// '/plugins/shared/httpd/figuren/htdocs/vendor'
	if ( isset( $_SERVER['X_FORWARDED_HOST'] ) && ! empty( $_SERVER['X_FORWARDED_HOST'] ) ) {
		$hostname = getenv( 'X_FORWARDED_HOST' );
	} else {
		$hostname = getenv( 'HTTP_HOST' );
	}
	$hostname = rtrim( (string) $hostname, '/' );

	$_needle = array(
		WP_CONTENT_URL . '/plugins' . FT_VENDOR_DIR,
		// 'https://' . \DOMAIN_CURRENT_SITE . FT_VENDOR_DIR,
		'https://' . $hostname . FT_VENDOR_DIR,
	);

	if ( strpos( $url, $_needle[0] ) !== false ) {
		$url = str_replace( $_needle[0], FT_VENDOR_URL, $url );
	}

	return $url;
}


/**
 * Retrieve the configuration for Altis.
 *
 * The configuration is defined by merging the defaults set by modules
 * with any overrides present in composer.json.
 *
 * @return array<mixed> Configuration data.
 */
function get_config() : array {

	$config = get_altis_config();

	if ( function_exists( 'apply_filters' ) ) {
		/**
		 * Filter the entire altis config.
		 *
		 * @param array $config The full config array.
		 */
		$config = apply_filters( 'Figuren_Theater.config', $config );
	}

	return $config;
}
