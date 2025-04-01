<?php
/**
 * @package     Daan\Theme
 * @description A better, Tailwindified implementation of the 'review' shortcode/block.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2025 Daan van den Bergh
 */

namespace Daan\Theme;

class Reviews {
	/**
	 * @param $atts
	 *
	 * @return false|string|void
	 */
	public function render( $atts ) {
		if ( ! function_exists( 'edd_reviews' ) ) {
			return;
		}

		ob_start();

		if ( isset( $atts[ 'multiple' ] ) && 'true' == $atts[ 'multiple' ] && isset( $atts[ 'number' ] ) && isset( $atts[ 'download' ] ) ) {
			$this->render_multiple_reviews( $atts );
		}

		return ob_get_clean();
	}

	private function render_multiple_reviews( $atts ) {
		$args = [
			'post_id'    => $atts[ 'download' ],
			'number'     => $atts[ 'number' ],
			'type'       => 'edd_review',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'edd_review_approved',
					'value'   => '1',
					'compare' => '=',
				],
				[
					'key'     => 'edd_review_approved',
					'value'   => 'spam',
					'compare' => '!=',
				],
				[
					'key'     => 'edd_review_approved',
					'value'   => 'trash',
					'compare' => '!=',
				],
			],
		];

		remove_action( 'pre_get_comments', [ edd_reviews(), 'hide_reviews' ] );

		$reviews = get_comments( $args );

		add_action( 'pre_get_comments', [ edd_reviews(), 'hide_reviews' ] );

		if ( empty( $reviews ) ) {
			return;
		} ?>

        <div class="daan-dev-reviews-shortcode grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
			<?php foreach ( $reviews as $review ): ?>
                <div class="<?php echo apply_filters(
					'edd_reviews_shortcode_class',
					'edd-review edd-review-body shadow-lg bg-white rounded-xl flex flex-col overflow-hidden block p-8'
				); ?>">
                    <div class="edd-review-meta flex flex-wrap items-center">
                        <div class="edd-review-author vcard font-brand font-bold mr-2"><strong><?php echo sprintf(
									'<span class="author">%s</span>',
									get_comment_author_link( $review->comment_ID )
								); ?></strong>
                        </div>
                        <span class="font-brand text-neutral-500 font-light lg:mr-auto"><?php echo get_comment_date(
								apply_filters( 'edd_reviews_widget_date_format', get_option( 'date_format' ) ),
								$review->comment_ID
							); ?></span>

                        <span class="edd-review-meta-rating flex w-full lg:w-auto order-first lg:order-last mb-2 lg:mb-0 text-primary-500"><?php edd_reviews()->render_star_rating(
								get_comment_meta( $review->comment_ID, 'edd_rating', true )
							); ?></span>
                    </div>
                    <div class="edd-review-content mt-4 line-clamp-4">
						<?php echo apply_filters( 'get_comment_text', $review->comment_content ); ?>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
		<?php
	}
}
