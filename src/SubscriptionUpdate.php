<?php
/**
 * @package     Daan\Theme
 * @description EDD Recurring's Update Payment Method page (/subscription/update/…), which isn't a real page.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class SubscriptionUpdate {
	/**
	 * Build class.
	 */
	public function __construct() {
		add_filter( 'body_class', [ $this, 'modify_body_class' ] );
		add_filter( 'wpseo_title', [ $this, 'modify_title' ] );
	}

	/**
	 * WordPress doesn't find a page for it, so it's a 404 until EDD Recurring's template takes over.
	 *
	 * @param string[] $classes
	 *
	 * @return string[]
	 */
	public function modify_body_class( $classes ) {
		if ( ! $this->is_update_page() ) {
			return $classes;
		}

		$classes   = array_diff( $classes, [ 'error404' ] );
		$classes[] = 'edd-subscription-update';

		return $classes;
	}

	/**
	 * EDD Recurring sets the title, but Yoast replaces it with its 404 title ("Page not found").
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function modify_title( $title ) {
		if ( ! $this->is_update_page() ) {
			return $title;
		}

		$page_title = get_query_var( 'edd_recurring_update' ) === 'success' ? __( 'Payment Method Updated', 'edd-recurring' ) : __( 'Update Payment Method', 'edd-recurring' );
		$separator  = function_exists( 'YoastSEO' ) ? YoastSEO()->helpers->options->get_title_separator() : '-';

		return sprintf( '%s %s %s', $page_title, $separator, get_bloginfo( 'name' ) );
	}

	/**
	 * Same check as EDD Recurring's Page::is_update_page().
	 *
	 * @return bool
	 */
	private function is_update_page() {
		return get_query_var( 'edd_recurring_update' ) && get_query_var( 'pagename' ) === 'subscription-update';
	}
}
