<?php
/**
 * Plugin Name: Paid Memberships Pro - Register Helper Admin
 * Plugin URI: https://github.com/trvswgnr/pmpro-register-helper-admin.git
 * Description: Add PMPro fields to checkout and user profile from the admin panel (used with Paid Memberships Pro Register Helper Add On).
 * Version: 1.1.3
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

require_once 'vendor/plugin-update-checker/plugin-update-checker.php';
require_once 'classes/class-register-helper-admin.php';
require_once 'classes/class-plugin-update-checker.php';

$pmpro_register_helper_admin = new PMPro\Register_Helper_Admin();

$update_checker = PMPro\Plugin_Update_Checker::buildUpdateChecker(
	'https://github.com/trvswgnr/pmpro-register-helper-admin/',
	__FILE__,
	'pmpro-register-helper-admin'
);

// set the branch that contains the stable release.
$update_checker->setBranch( 'master' );

$update_checker->getVcsApi()->enableReleaseAssets();

PMPro\Plugin_Update_Checker::auto_check_for_updates();
