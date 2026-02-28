<?php
/**
 * Template part for displaying a post's title
 *
 * @package kadence
 */

namespace Kadence;

$slug          = ( is_search() ? 'search' : get_post_type() );
$title_element = kadence()->option(
	$slug . '_archive_element_title',
	[
		'enabled' => true,
	]
);
if ( isset( $title_element ) && is_array( $title_element ) && true === $title_element['enabled'] ) {
	if ( ( ! is_post_type_archive( 'hs-docs-article' ) && ! is_tax( 'hs-docs-category' ) ) && ( is_search() && is_archive() || is_home() ) ) {
		the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
	} elseif ( is_search() || is_post_type_archive( 'hs-docs-article' ) || is_tax( 'hs-docs-category' ) ) {
		the_title(
			'<div class="entry-title md:flex md:gap-3 lg:gap-4 items-center md:mb-4"><a class="!text-neutral-900" href="' .
			esc_url( get_permalink() ) .
			'" rel="bookmark"><span class="dashicons dashicons-media-text"></span> ',
			'</a></div>'
		);
	} else {
		the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
	}
}
