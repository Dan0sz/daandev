<?php
/**
 * @package     Daan\Theme
 * @description A better, Tailwindified implementation of the 'Featured Downloads' shortcode.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2025 Daan van den Bergh
 */

namespace Daan\Theme;

use WP_Query;

class FeaturedDownloads {
	/**
	 * Render the shortcode.
	 *
	 * @return string|void
	 */
	public function render( $atts ) {
		$order_by   = $atts[ 'order_by' ] ?? 'post_date';
		$order      = $atts[ 'order' ] ?? 'DESC';
		$category   = $atts[ 'category' ] ?? '';
		$show_price = isset( $atts[ 'show_price' ] ) && $atts[ 'show_price' ] === 'true';

		if ( post_type_exists( 'download' ) ) {
			$post_type_obj = get_post_type_object( 'download' );
		}

		$args = apply_filters(
			'edd_featured_downloads_args',
			[
				'post_type'      => 'download',
				'orderby'        => $order_by,
				'order'          => $order,
				'posts_per_page' => 3,
				'meta_key'       => 'edd_feature_download',
			]
		);

		if ( $category ) {
			$args[ 'tax_query' ] = [
				[
					'taxonomy' => 'download_category',
					'field'    => 'slug',
					'terms'    => $category,
				],
			];

			unset( $args[ 'meta_key' ] );
		}

		$featured_downloads = new WP_Query( $args );

		ob_start();
		if ( $featured_downloads->have_posts() ) : $i = 1; ?>
            <div class="daan-featured-downloads grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-8">
				<?php while ( $featured_downloads->have_posts() ) : $featured_downloads->the_post(); ?>
                    <a itemscope itemtype="http://schema.org/Product" class="edd_download relative block rounded-xl md:rounded-3xl overflow-hidden !bg-white shadow-xl flex flex-col" id="edd_download_<?php echo get_the_ID(
					); ?>" href="<?php echo get_permalink( get_the_ID() ); ?>">
                        <div class="grid grid-cols-5 <?php echo $show_price ? 'aspect-16/9' : 'aspect-3/2'; ?>">
							<?php
							do_action( 'edd_download_before' );

							edd_get_template_part( 'shortcode', 'featured-downloads-image' );

							if ( ! $show_price ) {
								edd_get_template_part( 'shortcode', 'featured-downloads-button' );
							}

							do_action( 'edd_download_after' );
							?>
                        </div>
						<?php if ( $show_price ): ?>
							<?php edd_get_template_part( 'shortcode', 'featured-downloads-price' ); ?>
						<?php endif; ?>
                    </a>
				<?php endwhile; ?>
            </div>
		<?php endif;
		wp_reset_postdata();

		$html = ob_get_clean();

		return apply_filters( 'edd_fd_featured_downloads_html', $html, $featured_downloads );
	}
}
