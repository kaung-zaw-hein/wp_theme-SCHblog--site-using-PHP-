<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The elementor import functionality of the plugin.
 *
 * @package    Advanced_Import
 * @subpackage Advanced_Import/admin/Advanced_Import_Elementor
 * @author     Addons Press <addonspress.com>
 */
if ( ! class_exists( 'Advanced_Import_Elementor' ) ) {
	/**
	 * Advanced_Import_Elementor
	 */
	class Advanced_Import_Elementor {
		/**
		 * Main Advanced_Import_Elementor Instance
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @return object $instance Advanced_Import_Elementor Instance
		 */
		public static function instance() {

			// Store the instance locally to avoid private static replication.
			static $instance = null;

			// Only run these methods if they haven't been ran previously.
			if ( null === $instance ) {
				$instance = new self();
			}

			// Always return the instance.
			return $instance;
		}

		/**
		 * Change post id related to elementor to new id
		 *
		 * @param array $item    current array of demo list.
		 * @param string $key
		 * @return void
		 */
		public function elementor_id_import( &$item, $key ) {
			if ( $key == 'id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = advanced_import_admin()->imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'page' && ! empty( $item ) ) {

				if ( false !== strpos( $item, 'p.' ) ) {
					$new_id = str_replace( 'p.', '', $item );
					// check if this has been imported before
					$new_meta_val = advanced_import_admin()->imported_post_id( $new_id );
					if ( $new_meta_val ) {
						$item = 'p.' . $new_meta_val;
					}
				} elseif ( is_numeric( $item ) ) {
					// check if this has been imported before
					$new_meta_val = advanced_import_admin()->imported_post_id( $item );
					if ( $new_meta_val ) {
						$item = $new_meta_val;
					}
				}
			}
			if ( $key == 'post_id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = advanced_import_admin()->imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'url' && ! empty( $item ) && strstr( $item, 'ocalhost' ) ) {
				// check if this has been imported before
				$new_meta_val = advanced_import_admin()->imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( ( $key == 'shortcode' || $key == 'editor' ) && ! empty( $item ) ) {
				// we have to fix the [contact-form-7 id=133] shortcode issue.
				$item = advanced_import_admin()->parse_shortcode_meta_content( $item );

			}
		}

		public function elementor_post( $post_id = false ) {

			// regenerate the CSS for this Elementor post
			if ( class_exists( 'Elementor\Core\Files\CSS\Post' ) ) {
				$post_css = new Elementor\Core\Files\CSS\Post( $post_id );
				$post_css->update();
			}
		}
	}
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function advanced_import_elementor() {
	return Advanced_Import_Elementor::instance();
}
