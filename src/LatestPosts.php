<?php
/**
 * @package     Daan\Theme
 * @description A better, Tailwindified implementation of the 'Latest Posts' block.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2025 Daan van den Bergh
 */

namespace Daan\Theme;

use WP_Query;

class LatestPosts {
	/**
	 * Overwrites @see render_block_core_latest_posts()
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function render( $attributes ) {
		global $post, $block_core_latest_posts_excerpt_length;

		$args = [
			'posts_per_page'      => $attributes[ 'postsToShow' ],
			'post_status'         => 'publish',
			'order'               => $attributes[ 'order' ],
			'orderby'             => $attributes[ 'orderBy' ],
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];

		$block_core_latest_posts_excerpt_length = $attributes[ 'excerptLength' ];
		add_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

		if ( ! empty( $attributes[ 'categories' ] ) ) {
			$args[ 'category__in' ] = array_column( $attributes[ 'categories' ], 'id' );
		}
		if ( isset( $attributes[ 'selectedAuthor' ] ) ) {
			$args[ 'author' ] = $attributes[ 'selectedAuthor' ];
		}

		$query        = new WP_Query();
		$recent_posts = $query->query( $args );

		if ( isset( $attributes[ 'displayFeaturedImage' ] ) && $attributes[ 'displayFeaturedImage' ] ) {
			update_post_thumbnail_cache( $query );
		}

		$list_items_markup = '';

		foreach ( $recent_posts as $post ) {
			$post_link = esc_url( get_permalink( $post ) );
			$title     = get_the_title( $post );

			if ( ! $title ) {
				$title = __( '(no title)' );
			}

			$list_items_markup .= "<a class='shadow-lg !bg-white rounded-xl flex flex-col overflow-hidden block' href='$post_link'>";

			if ( $attributes[ 'displayFeaturedImage' ] && has_post_thumbnail( $post ) ) {
				$image_style = '';

				if ( isset( $attributes[ 'featuredImageSizeWidth' ] ) ) {
					$image_style .= sprintf( 'max-width:%spx;', $attributes[ 'featuredImageSizeWidth' ] );
				}
				if ( isset( $attributes[ 'featuredImageSizeHeight' ] ) ) {
					$image_style .= sprintf( 'max-height:%spx;', $attributes[ 'featuredImageSizeHeight' ] );
				}

				$image_classes = 'shrink-0 !rounded-none rounded lg:rounded-lg mb-2 lg:mb-3 overflow-hidden';

				if ( isset( $attributes[ 'featuredImageAlign' ] ) ) {
					$image_classes .= ' align' . $attributes[ 'featuredImageAlign' ];
				}

				$featured_image = get_the_post_thumbnail(
					$post,
					'daan-dev-latest-posts',
					[
						'style' => esc_attr( $image_style ),
						'class' => 'max-w-none w-full h-full absolute inset-0 object-cover opacity-0 transition duration-75 ease-in opacity-100',
					]
				);

				$list_items_markup .= sprintf(
					'<div class="%1$s"><figure class="relative aspect-16/10">%2$s</figure></div>',
					esc_attr( $image_classes ),
					$featured_image
				);
			}

			$list_items_markup .= '<div class="p-4 flex flex-col h-full">';

			$list_items_markup .= sprintf(
				'<div class="font-brand font-semibold text-lg md:text-xl text-(--color-black)" href="%1$s">%2$s</div>',
				esc_url( $post_link ),
				$title
			);

			if ( isset( $attributes[ 'displayAuthor' ] ) && $attributes[ 'displayAuthor' ] ) {
				$author_display_name = get_the_author_meta( 'display_name', $post->post_author );

				/* translators: byline. %s: author. */
				$byline = sprintf( __( 'by %s' ), $author_display_name );

				if ( ! empty( $author_display_name ) ) {
					$list_items_markup .= sprintf(
						'<div class="wp-block-latest-posts__post-author">%1$s</div>',
						$byline
					);
				}
			}

			if ( isset( $attributes[ 'displayPostDate' ] ) && $attributes[ 'displayPostDate' ] ) {
				$list_items_markup .= sprintf(
					'<time datetime="%1$s" class="wp-block-latest-posts__post-date text-xs md:text-sm text-neutral-800 mt-1">%2$s</time>',
					esc_attr( get_the_date( 'c', $post ) ),
					get_the_date( '', $post )
				);
			}

			$list_items_markup .= sprintf(
			/* translators: 1: A URL to a post, 2: Hidden accessibility text: Post title */ __(
				'<button class="font-brand font-semibold transition disabled:opacity-50 focus:outline-none text-center editor-noclick text-(--color-primary-500) border-(--color-transparent) hover:border-(--color-primary-500) text-sm lg:text-base border-b inline-flex items-center gap-2 md:mt-auto pt-4 w-fit">Read more<span class="screen-reader-text">: %1$s</span> %2$s</button>'
			),
				esc_html( $title ),
				'<svg class="fill-current w-[1em] h-[1em] inline-block" viewBox="0 0 448 512"><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"></path></svg>'
			);

			$list_items_markup .= '</div>';
			$list_items_markup .= "</a>\n";
		}

		remove_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

		$classes = [ 'wp-block-latest-posts__list' ];
		if ( isset( $attributes[ 'postLayout' ] ) && 'grid' === $attributes[ 'postLayout' ] ) {
			$classes[] = 'is-grid';
		}
		if ( isset( $attributes[ 'columns' ] ) && 'grid' === $attributes[ 'postLayout' ] ) {
			$classes[] = 'columns-' . $attributes[ 'columns' ];
		}
		if ( isset( $attributes[ 'displayPostDate' ] ) && $attributes[ 'displayPostDate' ] ) {
			$classes[] = 'has-dates';
		}
		if ( isset( $attributes[ 'displayAuthor' ] ) && $attributes[ 'displayAuthor' ] ) {
			$classes[] = 'has-author';
		}
		if ( isset( $attributes[ 'style' ][ 'elements' ][ 'link' ][ 'color' ][ 'text' ] ) ) {
			$classes[] = 'has-link-color';
		}

		$classes[] = 'grid';
		$classes[] = 'gap-4';
		$classes[] = 'md:gap-8';
		$classes[] = 'grid-cols-1';
		$classes[] = 'md:grid-cols-3';

		$wrapper_attributes = get_block_wrapper_attributes( [ 'class' => implode( ' ', $classes ) ] );

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			$list_items_markup
		);
	}
}
