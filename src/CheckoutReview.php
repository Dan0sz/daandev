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
            <div class="edd-review edd-review-body">
				<?php Reviews::render_review_content( $review, 48, false ); ?>
            </div>
        </section>
		<?php
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
