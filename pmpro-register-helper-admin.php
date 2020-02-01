<?php
/**
 * Plugin Name: Paid Memberships Pro - Register Helper Admin
 * Plugin URI: https://github.com/trvswgnr/pmpro-register-helper-admin.git
 * Description: Add PMPro fields to checkout and user profile from the admin panel (used with Paid Memberships Pro Register Helper Add On).
 * Version: 1.1.0
 * Author: Travis Aaron Wagner
 * Author URI: https://travisaw.com
 * Text Domain: pmprorha
 *
 * @package pmprorha
 */

// prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PMPRORHA_DIR', dirname( __FILE__ ) );

require_once PMPRORHA_DIR . '/classes/class-register-helper-admin.php';

$pmpro_register_helper_admin = new PMPro\Register_Helper_Admin();

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/trvswgnr/pmpro-register-helper-admin/',
	__FILE__,
	'pmpro-register-helper-admin'
);

// set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'master' );
