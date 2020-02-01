<?php
/**
 * Plugin Name: Paid Memberships Pro - Register Helper Admin
 * Plugin URI: https://travisaw.com
 * Description: Add PMPro fields to checkout and user profile from the admin panel (used with Paid Memberships Pro Register Helper Add On).
 * Version: 1.0.0
 * Author: Travis Aaron Wagner
 * Author URI: https://travisaw.com
 * Text Domain: pmpro-register-helper-admin
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
