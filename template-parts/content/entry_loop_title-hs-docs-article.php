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
if ( isset( $title_element ) && is_array( $title_element ) && true === $title_element[ 'enabled' ] ) {
	if ( ( ! is_post_type_archive( 'hs-docs-article' ) && ! is_tax( 'hs-docs-category' ) ) && ( is_search() || is_archive() || is_home() ) ) {
		the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
	} elseif ( is_post_type_archive( 'hs-docs-article' ) || is_tax( 'hs-docs-category' ) ) {
		the_title(
			'<div class="entry-title flex gap-3 lg:gap-4 items-center mb-4 text-md font-brand"><a class="!text-neutral-900" href="' .
			esc_url( get_permalink() ) .
			'" rel="bookmark"><svg class="fill-current w-[1em] h-[1em] inline-block text-primary-500 align-top mt-1 mr-1" viewBox="0 0 384 512"><path d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-288-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zM112 256l160 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-160 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64l160 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-160 0c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64l160 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-160 0c-8.8 0-16-7.2-16-16s7.2-16 16-16z"></path></svg>',
			'</a></div>'
		);
	} else {
		the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
	}
}
