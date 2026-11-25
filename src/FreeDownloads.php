<?php
/**
 * @package     Daan\Theme
 * @description Modifications for the product pages of free plugins, which are downloaded from wordpress.org.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class FreeDownloads {
	/**
	 * Build class.
	 */
	public function __construct() {
		add_filter( 'widget_display_callback', [ $this, 'modify_widgets' ], 10, 2 );
	}

	/**
	 * Free plugins have no license to choose, and no changelog (of EDD Software Licensing) to view.
	 *
	 * @param array|false $instance
	 * @param \WP_Widget  $widget
	 *
	 * @return array|false
	 */
	public function modify_widgets( $instance, $widget ) {
		if ( ! is_array( $instance ) || ! self::is_free_download() ) {
			return $instance;
		}

		if ( 'edd-sl-download-details' === $widget->id_base ) {
			return false;
		}

		if ( 'edd_product_details' === $widget->id_base ) {
			$instance['title'] = __( 'Get it for free', 'daandev' );
		}

		return $instance;
	}

	/**
	 * Is this the product page of a plugin that's downloaded from wordpress.org?
	 *
	 * @return bool
	 */
	public static function is_free_download() {
		if ( ! is_singular( 'download' ) ) {
			return false;
		}

		$url = get_post_meta( get_queried_object_id(), '_edd_external_product_url', true );

		return in_array( wp_parse_url( (string) $url, PHP_URL_HOST ), [ 'wordpress.org', 'www.wordpress.org' ], true );
	}
}
