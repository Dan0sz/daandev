<?php
/**
 * @package     Daan\Theme
 * @description Average star rating and number of reviews of a download, e.g. "★★★★★ 5.0 (71 reviews)".
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class ReviewSummary {
	/**
	 * Build class.
	 */
	public function __construct() {
		// Right below the "Choose your license" title of the Product Details widget on download pages.
		add_action( 'edd_product_details_widget_before_title', [ $this, 'render_in_widget' ], 10, 2 );
	}

	/**
	 * @param array $instance
	 * @param int   $download_id
	 *
	 * @return void
	 */
	public function render_in_widget( $instance, $download_id ) {
		echo self::render( $download_id, 'daan-review-summary--widget', get_permalink( $download_id ) . '#edd-reviews' );
	}

	/**
	 * Average rating and number of approved reviews (replies not included). Cached for an hour.
	 *
	 * @param int $download_id
	 *
	 * @return array [ 'average' => float, 'count' => int ]
	 */
	public static function get( $download_id ) {
		global $wpdb;

		$key     = 'daan_review_summary_' . $download_id;
		$summary = get_transient( $key );

		if ( false === $summary ) {
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT AVG(rating.meta_value) AS average, COUNT(*) AS count
					FROM {$wpdb->comments} c
					JOIN {$wpdb->commentmeta} rating ON rating.comment_id = c.comment_ID AND rating.meta_key = 'edd_rating'
					JOIN {$wpdb->commentmeta} approved ON approved.comment_id = c.comment_ID AND approved.meta_key = 'edd_review_approved' AND approved.meta_value = '1'
					LEFT JOIN {$wpdb->commentmeta} reply ON reply.comment_id = c.comment_ID AND reply.meta_key = 'edd_review_reply'
					WHERE c.comment_post_ID = %d AND c.comment_type = 'edd_review' AND c.comment_approved = '1' AND reply.comment_id IS NULL",
					$download_id
				)
			);

			$summary = [
				'average' => round( (float) ( $row->average ?? 0 ), 1 ),
				'count'   => (int) ( $row->count ?? 0 ),
			];

			set_transient( $key, $summary, HOUR_IN_SECONDS );
		}

		return $summary;
	}

	/**
	 * @param int    $download_id
	 * @param string $class Extra class(es) for the wrapper.
	 * @param string $url   Link to the reviews; leave empty when the summary is already inside a link (e.g. a card).
	 *
	 * @return string Empty if the download has no reviews.
	 */
	public static function render( $download_id, $class = '', $url = '' ) {
		if ( ! function_exists( 'edd_reviews' ) ) {
			return '';
		}

		$summary = self::get( $download_id );

		if ( ! $summary[ 'count' ] ) {
			return '';
		}

		$tag     = $url ? 'a' : 'span';
		$average = number_format_i18n( $summary[ 'average' ], 1 );
		/* translators: %s: number of reviews. */
		$count = sprintf( _n( '%s review', '%s reviews', $summary[ 'count' ], 'daandev' ), number_format_i18n( $summary[ 'count' ] ) );
		/* translators: 1: average rating, e.g. 4.8, 2: "71 reviews". */
		$label = sprintf( __( 'Rated %1$s out of 5, based on %2$s', 'daandev' ), $average, $count );

		ob_start();
		edd_reviews()->render_star_rating( round( $summary[ 'average' ] ) );
		$stars = ob_get_clean();

		return sprintf(
			'<%1$s class="daan-review-summary %2$s"%3$s aria-label="%4$s"><span class="daan-review-summary__stars" aria-hidden="true">%5$s</span><span class="daan-review-summary__average" aria-hidden="true">%6$s</span><span class="daan-review-summary__count" aria-hidden="true">(%7$s)</span></%1$s>',
			$tag,
			esc_attr( $class ),
			$url ? ' href="' . esc_url( $url ) . '"' : ' role="img"',
			esc_attr( $label ),
			$stars,
			esc_html( $average ),
			esc_html( $count )
		);
	}
}
