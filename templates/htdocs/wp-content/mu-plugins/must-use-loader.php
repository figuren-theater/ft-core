<?php # -*- coding: utf-8 -*-
/*
* Plugin Name:  must-use-loader for the figuren.theater plattform
* Description:  Autoloads ft-modules from subfolders
* Plugin URI:   https://github.com/figuren-theater/ft-core
* Version:      2023.01.22
* Author:       figuren.theater
* Author URI:   https://figuren.theater
* License:      GPL-3.0-or-later
*/

declare(strict_types=1);

namespace Figuren_Theater;

use FT_VENDOR_DIR;

use WP_PLUGIN_DIR;
use WPMU_PLUGIN_DIR;

use function add_action;
use function add_filter;
use function do_action;
use function get_current_blog_id;
use function get_current_network_id;


const FT_PACKAGES = [
	'ft-admin-ui',
	'ft-core', // this will be loaded first
	'ft-data',
	'ft-interactive',
	'ft-maintenance',
	'ft-media',
	'ft-onboarding',
	// 'ft-options',
	'ft-performance',
	'ft-privacy',
	'ft-routes',
	'ft-security',
	'ft-seo',
	'ft-site-editing',
	'ft-theming',
];


defined( 'ABSPATH' ) || exit;

add_action( 'mu_plugin_loaded', function () {

	// load modules one by one
	// should be done by composer 
	array_map(
		function ( string $package ) {
			// the path-part "FT"
			// is defined inside composer.json
			// as "extra:installer-paths"
			require_once "FT/{$package}/plugin.php";
		},
		FT_PACKAGES
	);

	// load network specific mu-plugins
	$_network_mu_plugin_loader = 'mu-per-network/' . get_current_network_id() . '/namespace.php';
	if ( file_exists( WPMU_PLUGIN_DIR . '/' . $_network_mu_plugin_loader ) )
		require_once $_network_mu_plugin_loader;

	// load blog specific mu-plugins
	$_blog_mu_plugin_loader = 'mu-per-blog/' . get_current_blog_id() . '/namespace.php';
	if ( file_exists( WPMU_PLUGIN_DIR . '/' . $_blog_mu_plugin_loader ) )
		require_once $_blog_mu_plugin_loader;

	// old pseudo-oop hoorror
	// needs to be removed
	// one by one and over time ...
	require_once 'Figuren_Theater/Figuren_Theater.php';

	// 
	do_action( __NAMESPACE__ . '\\init', FT::site() );

	// trigger module registration
	do_action( 'altis.loaded_autoloader' );

	//
	do_action( __NAMESPACE__ . '\\loaded', FT::site() );

} );



//////////////////////////////////
// temporary TEST & DEBUG stuff //
//////////////////////////////////


function load_script_textdomain_relative_path( string $relative, string $src  ) : string {

	// if ('wordpress-seo/js/dist/externals/helpers.js' === $relative ) {
	\do_action( 'qm/warning', 'load_script_textdomain_relative_path()' );

	// \do_action( 'qm/debug', [ $relative, $src ] );
		// $relative = FT_VENDOR_DIR . '/wpackagist-plugin/' . $relative;
	\do_action( 'qm/debug', [ $relative, $src ] );

	$vendor = str_replace(
		WP_CONTENT_URL . '/v/', 
		'',
		str_replace(
			'/' . $relative,
			'',
			$src
		)
	);
	$textdomain = explode('/', $relative)[0];

	\do_action( 'qm/debug', $vendor );
	\do_action( 'qm/debug', $textdomain );



	add_filter( 
		'load_script_translation_file', 
		function( string $file, string $handle, string $domain ) use ( $vendor, $textdomain ) : string {
			
			// if it is not our textdomain
			if ( $domain !== $textdomain )
				return $file;
			
			// if does not start wrong, return
			if ( 0 !== strpos( $file, \WP_LANG_DIR . '/v' ) )
				return $file;

			$filename = str_replace(
				\WP_LANG_DIR . '/v/',
				'',
				$file
			);

			// everything ok
			// lets change a path
			// 
			// THIS IS BETTER GUESSING,
			// but nothing more ... **** it
			\do_action( 'qm/error', [ $file, $handle, $domain, $vendor, $textdomain ] );		
			$file = FT_VENDOR_DIR . '/' . $vendor . '/' . $textdomain . '/languages/' . $filename;
			\do_action( 'qm/info', [ $file, file_exists($file) ] );		

			return $file;
		},
		1001,
		3
	);


	// }

	return $relative;
}
// add_filter( 'load_script_textdomain_relative_path', __NAMESPACE__ . '\\load_script_textdomain_relative_path', 1000, 3 );


/**
 * !!!
 *
 * @package [package]
 * @since   3.0
 *
 * @param   string    $mofile [description]
 * @param   string    $domain [description]
 * @return  [type]            [description]
 */
function load_textdomain_mofile( string $mofile, string $domain  ) : string {

	// \do_action( 'qm/warning', 'load_textdomain_mofile()' );
	// \do_action( 'qm/debug', [ $mofile, $domain ] );
	// if ('ft-network-block-patterns' === $domain ) {
	// }

	// looking for 
	// /shared/httpd/figuren/htdocs/content/plugins/shared/httpd/figuren/htdocs/vendor/
	$search = WP_PLUGIN_DIR . FT_VENDOR_DIR;

	if (0 === strpos($mofile, $search)) {
		$mofile = str_replace( $search, FT_VENDOR_DIR, $mofile );
	}

	return $mofile;
}
add_filter( 'load_textdomain_mofile', __NAMESPACE__ . '\\load_textdomain_mofile', 1000, 3 );


/**
 * Filters the file path for loading script translations for the given script handle and text domain.
 *
 * Should help against misleading i18n urls for json files, like:
 * - content/languages/v/abbreviation-button-for-the-block-editor-de_DE-905251f92e3c6581795dad44a8d7902c.json
 * - content/languages/v/wordpress-seo-de_DE-af13d5bac4604f14038e81c783c6c67a.json
 * - content/languages/v/koko-analytics-de_DE-e6ea4fe3eb014300d65a6b5b667706a9.json
 *
 * @since 5.0.2
 *
 * @param string|false $file   Path to the translation file to load. False if there isn't one.
 * @param string       $handle Name of the script to register a translation domain to.
 * @param string       $domain The text domain.
 */
function load_script_translation_file( string $file, string $handle, string $domain ) : string {

	$file_exists = file_exists($file);


	if ( ! $file_exists ) {
		\do_action( 'qm/warning', 'load_script_translation_file()' );
		\do_action( 'qm/debug', [ $file, $handle, $domain ] );
		#\do_action( 'qm/debug', 'file_exists(): {file_exists}', [
		#    'file_exists' => ,
		#] );
	}
	/*

	$file = str_replace(
		'content/languages/v/',
		'content/languages/plugins/',
		$file
	);
	$file = str_replace(
		'content/pluginsvendor',
		// 'content/v',
		'vendor',
		$file
	);*/

	return $file;
}
// add_filter( 'load_script_translation_file', __NAMESPACE__ . '\\load_script_translation_file', 1000, 3 );
