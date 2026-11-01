<?php
/**
 * @package     Daan\Theme
 * @description AffiliateWP's Affiliate Area, styled like the account pages (EDD Better Account).
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class AffiliateArea {
	/**
	 * Build class.
	 */
	public function __construct() {
		add_filter( 'body_class', [ $this, 'modify_body_class' ] );
		add_filter( 'affwp_render_affiliate_dashboard_tab', [ $this, 'wrap_tab' ] );
		add_action( 'wp_footer', [ $this, 'scroll_to_active_tab' ] );
	}

	/**
	 * @param string[] $classes
	 *
	 * @return string[]
	 */
	public function modify_body_class( $classes ) {
		if ( $this->is_affiliate_area() ) {
			$classes[] = 'has-affiliate-area';
		}

		return $classes;
	}

	/**
	 * Some tabs (e.g. Statistics) consist of several parts, which should be on one card.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function wrap_tab( $content ) {
		return '<div class="affiliate-area__content">' . $content . '</div>';
	}

	/**
	 * On smaller screens the tabs scroll sideways, so make sure the active tab is visible.
	 *
	 * @return void
	 */
	public function scroll_to_active_tab() {
		if ( ! $this->is_affiliate_area() ) {
			return;
		}
		?>
		<script>
			( () => {
				const tabs = document.getElementById( 'affwp-affiliate-dashboard-tabs' );
				const active = tabs?.querySelector( '.active' );

				if ( active && tabs.scrollWidth > tabs.clientWidth ) {
					tabs.scrollLeft = active.offsetLeft - ( tabs.clientWidth - active.offsetWidth ) / 2;
				}
			} )();
		</script>
		<?php
	}

	/**
	 * @return bool
	 */
	private function is_affiliate_area() {
		return is_page() && function_exists( 'affwp_is_affiliate_area' ) && affwp_is_affiliate_area();
	}
}
