<?php
/**
 * @package     Daan\Theme
 * @description A child theme based on Kadence with Tailwind CSS.
 * @author      Daan van den Bergh
 * @company     Daan.dev
 * @copyright   2025 Daan van den Bergh
 * @license     MIT
 */

use Daan\Branding\Admin\Downloads\Editor\Branding;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Get the Brand.
 *
 * @param $id
 *
 * @return mixed
 */
function daan_get_brand( $id = '' ) {
	$brand = daan_get_branding( $id, Branding::BRAND ) ?: 'daandev';

	switch ( $brand ) {
		case 'omgf':
			$elements = [
				'from-light' => 'from-omgf-light',
				'from-dark'  => 'from-omgf-dark',
				'to-light'   => 'to-omgf-light',
				'to-dark'    => 'to-omgf-dark',
			];
			break;
		case 'caos':
			$elements = [
				'from-light' => 'from-caos-light',
				'from-dark'  => 'from-caos-dark',
				'to-light'   => 'to-caos-light',
				'to-dark'    => 'to-caos-dark',
			];
			break;
		case 'gdpress':
			$elements = [
				'from-light' => 'from-gdpress-light',
				'from-dark'  => 'from-gdpress-dark',
				'to-light'   => 'to-gdpress-light',
				'to-dark'    => 'to-gdpress-dark',
			];
			break;
		default:
			$elements = [
				'from-light' => 'from-daandev-light',
				'from-dark'  => 'from-daandev-dark',
				'to-light'   => 'to-daandev-light',
				'to-dark'    => 'to-daandev-dark',
			];
	}

	return $elements;
}

/**
 * Get the Logo.
 *
 * @param $id
 *
 * @return mixed
 */
function daan_get_logo( $id = '' ) {
	return daan_get_branding( $id );
}

/**
 * Get the Logo Light.
 *
 * @param $id
 *
 * @return mixed
 */
function daan_get_logo_light( $id = '' ) {
	return daan_get_branding( $id, Branding::LOGO_LIGHT );
}

/**
 * Get the Icon.
 *
 * @param $id
 *
 * @return mixed
 */
function daan_get_icon( $id = '' ) {
	return daan_get_branding( $id, Branding::ICON );
}

/**
 * Get the Tagline.
 *
 * @param $id
 *
 * @return mixed
 */
function daan_get_tagline( $id = '' ) {
	return daan_get_branding( $id, Branding::TAGLINE );
}

/**
 * Get the Button Text.
 *
 * @param $id
 *
 * @return mixed
 */
function daan_get_button_text( $id = '' ) {
	return daan_get_branding( $id, Branding::BUTTON_TEXT );
}

/**
 * Wrapper for getting Post Meta, basically.
 *
 * @param $id
 * @param $element
 *
 * @return mixed
 */
function daan_get_branding( $id = '', $element = Branding::LOGO_NORMAL ) {
	if ( ! $id ) {
		$id = get_the_ID();
	}

	return get_post_meta( $id, $element, true );
}

/**
 * Returns the lowest price for an EDD Download.
 *
 * @param $id
 *
 * @return mixed|string
 */
function daan_get_lowest_price( $id ) {
	if ( ! function_exists( 'edd_has_variable_prices' ) ) {
		return '';
	}

	if ( ! edd_has_variable_prices( $id ) ) {
		return edd_price( $id, false );
	}

	$prices = edd_get_variable_prices( $id );

	return $prices[ array_key_first( $prices ) ];
}

new \Daan\Theme\Theme();
