<?php

/**
 * Plugin Name: VIP Go Core Modifications
 * Description: Changes to make WordPress core work better on VIP Go.
 * Author: Automattic
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

require_once( __DIR__ . '/001-core/privacy.php' );

/**
 * Disable current theme validation
 *
 * By default, WordPress falls back to a default theme if it can't find
 * the active theme. This is undesirable because it requires manually
 * re-activating the correct theme and can lead to data loss in the form
 * of deactivated widgets and menu location assignments.
 */
add_filter( 'validate_current_theme', '__return_false' );

if ( false !== WPCOM_IS_VIP_ENV ) {
	add_action( 'muplugins_loaded', 'wpcom_vip_init_core_restrictions' );
}

function wpcom_vip_init_core_restrictions() {
	add_action( 'admin_init', 'wpcom_vip_disable_core_update_nag' );
	add_filter( 'map_meta_cap', 'wpcom_vip_disable_core_update_cap', 100, 2 );
}

function wpcom_vip_disable_core_update_nag() {
	remove_action( 'admin_notices', 'update_nag', 3 );
	remove_action( 'network_admin_notices', 'update_nag', 3 );
}

function wpcom_vip_disable_core_update_cap( $caps, $cap ) {
	if ( 'update_core' === $cap ) {
		$caps = [ 'do_not_allow' ];
	}
	return $caps;
}

/**
 * Disable tests in the Site Health (AKA site status) tool page
 *
 * By default, WordPress runs a series of tests on the Site Health tool
 * page in wp-admin. This disables all irrelevant or unnecessary tests.
 */
function vip_disable_unnecessary_site_health_tests( $tests ) {
	// Disable "Background Updates" test.
	// WordPress updates are managed by the VIP team.
	if ( isset( $tests['async'] ) && isset( $tests['async']['background_updates'] ) ) {
		unset( $tests['async']['background_updates'] );
	}

	return $tests;
}

add_filter( 'site_status_tests', 'vip_disable_unnecessary_site_health_tests' );

/**
 * Filter PHP modules in the Site Health (AKA site status) tool page
 *
 * By default, WordPress runs php_extension tests on the Site Health tool
 * page in wp-admin. This filters out all irrelevant or unnecessary PHP modules
 * within the test.
 */
function vip_filter_unnecessary_php_modules_for_site_health_tests( $modules ) {
	// Remove 'exif' PHP module.
	if ( isset( $modules['exif'] ) ) {
		unset( $modules['exif'] );
	}

	// Remove 'imagick' PHP module.
	if ( isset( $modules['imagick'] ) ) {
		unset( $modules['imagick'] );
	}

	return $modules;
}

add_filter( 'site_status_test_php_modules', 'vip_filter_unnecessary_php_modules_for_site_health_tests' );

