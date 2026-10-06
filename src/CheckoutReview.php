<?php
/**
 * @package     Daan\Theme
 * @description Shows the latest 5-star review of a product in the cart below the checkout cart.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class CheckoutReview {
	/**
	 * Only reviews with this rating are shown.
	 */
	const RATING = 5;

	/**
	 * Build class.
	 */
	public function __construct() {
		// After the license renewal form (priority 10), which EDD Software Licensing adds to the same hook.
		add_action( 'edd_after_checkout_cart', [ $this, 'render' ], 20 );
	}

	/**
	 * Render the latest 5-star review of one of the products in the cart, if there is one.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! function_exists( 'edd_reviews' ) ) {
			return;
		}

		$review = $this->get_review();

		if ( ! $review ) {
			return;
		} ?>
        <section class="daan-checkout-review" aria-label="<?php echo esc_attr(
			sprintf( __( 'Review of %s', 'daandev' ), get_the_title( $review->comment_post_ID ) )
		); ?>">
            <div class="edd-review-author">
				<?php // By email: WordPress only shows avatars for the 'comment' type, not for 'edd_review'. ?>
				<?php echo get_avatar( $review->comment_author_email, 48 ); ?>
                <b><?php echo esc_html( get_comment_meta( $review->comment_ID, 'edd_review_title', true ) ); ?></b>
                <span class="edd-review-meta-rating"><?php edd_reviews()->render_star_rating(
						get_comment_meta( $review->comment_ID, 'edd_rating', true )
					); ?></span>
            </div>
            <div class="edd-review-content" id="daan-checkout-review-content">
				<?php echo apply_filters( 'get_comment_text', $review->comment_content ); ?>
            </div>
            <button type="button" class="daan-checkout-review__toggle" aria-controls="daan-checkout-review-content" aria-expanded="false" aria-label="<?php esc_attr_e(
				'Read more',
				'daandev'
			); ?>" hidden><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
        </section>
		<?php
		wp_print_inline_script_tag( $this->get_toggle_script() );
	}

	/**
	 * Long reviews are cut off after a few lines: show an arrow to expand them, but only when the text is actually cut off.
	 *
	 * @return string
	 */
	private function get_toggle_script() {
		$labels = wp_json_encode( [ 'more' => __( 'Read more', 'daandev' ), 'less' => __( 'Read less', 'daandev' ) ] );

		return <<<JS
( function () {
	const labels  = $labels;
	const section = document.querySelector( '.daan-checkout-review' );
	const content = section && section.querySelector( '.edd-review-content' );
	const toggle  = section && section.querySelector( '.daan-checkout-review__toggle' );

	if ( ! toggle || content.scrollHeight <= content.clientHeight + 1 ) {
		return;
	}

	toggle.hidden = false;
	toggle.addEventListener( 'click', function () {
		const expanded = section.classList.toggle( 'is-expanded' );

		toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', expanded ? labels.less : labels.more );
	} );
} )();
JS;
	}

	/**
	 * @return \WP_Comment|null
	 */
	private function get_review() {
		$download_ids = array_unique( array_filter( array_map( 'absint', wp_list_pluck( edd_get_cart_contents() ?: [], 'id' ) ) ) );

		if ( empty( $download_ids ) ) {
			return null;
		}

		// EDD Reviews hides reviews from regular comment queries.
		remove_action( 'pre_get_comments', [ edd_reviews(), 'hide_reviews' ] );

		$reviews = get_comments(
			[
				'post__in'   => $download_ids,
				'type'       => 'edd_review',
				'status'     => 'approve',
				'parent'     => 0,
				'number'     => 1,
				'orderby'    => 'comment_date_gmt',
				'order'      => 'DESC',
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'   => 'edd_rating',
						'value' => self::RATING,
					],
					[
						'key'   => 'edd_review_approved',
						'value' => '1',
					],
				],
			]
		);

		add_action( 'pre_get_comments', [ edd_reviews(), 'hide_reviews' ] );

		return $reviews ? $reviews[0] : null;
	}
}
