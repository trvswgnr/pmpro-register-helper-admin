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

require_once 'classes/class-register-helper-admin.php';

$pmpro_register_helper_admin = new PMPro\Register_Helper_Admin();
