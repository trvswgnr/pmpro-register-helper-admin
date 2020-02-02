<?php
/**
 * PMPRo Plugin Update Checker
 *
 * @package pmprorha
 * @since 1.1.2
 */

namespace PMPro;

if ( class_exists( 'Puc_v4_Factory', false ) ) {
	class Plugin_Update_Checker extends \Puc_v4_Factory {
		/**
		 * Automatically check for updates.
		 */
		public static function auto_check_for_updates() {
			global $pagenow;
			$url              = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$has_params       = ! empty( $_GET ) ? true : false;
			$has_update_check = isset( $_GET['puc_check_for_updates'] ) ? true : false;

			if ( $has_params ) {
				$url .= '&puc_check_for_updates=1';
			} else {
				$url .= '?puc_check_for_updates=1';
			}

			if ( ! $has_update_check && 'plugins.php' === $pagenow ) {
				Header( 'Location: ' . $url );
				exit();
			}
		}
	}
}
