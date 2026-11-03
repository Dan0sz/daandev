<?php
/**
 * @package     Daan\Theme
 * @description Comment pages (…/comment-page-2/, ?cpage=2), e.g. of the reviews on product pages, aren't indexed and
 *              don't exist past the last page.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class CommentPages {
	/**
	 * Build class.
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'maybe_404' ] );
		add_filter( 'wpseo_robots', [ $this, 'modify_robots' ] );
	}

	/**
	 * WordPress shows the post for any comment page, e.g. …/comment-page-40/ or on posts without comments.
	 *
	 * @return void
	 */
	public function maybe_404() {
		$cpage = $this->get_cpage();

		if ( $cpage < 2 || $cpage <= $this->get_pages_count() ) {
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * The comments are on the post itself, so don't index each page of them.
	 *
	 * @param string $robots
	 *
	 * @return string
	 */
	public function modify_robots( $robots ) {
		if ( $this->get_cpage() < 1 ) {
			return $robots;
		}

		return 'noindex, follow';
	}

	/**
	 * @return int The requested comment page, 0 if this isn't a comment page.
	 */
	private function get_cpage() {
		if ( ! is_singular() ) {
			return 0;
		}

		return (int) get_query_var( 'cpage' );
	}

	/**
	 * The number of comment pages: of the reviews (EDD Reviews) on product pages, of the comments elsewhere.
	 *
	 * @return int
	 */
	private function get_pages_count() {
		if ( ! get_option( 'page_comments' ) ) {
			return 1;
		}

		$post_id  = get_queried_object_id();
		$per_page = max( 1, (int) get_option( 'comments_per_page' ) );
		$pages    = get_comment_pages_count( get_comments( [ 'post_id' => $post_id, 'status' => 'approve', 'include_unapproved' => $this->get_unapproved_authors() ] ), $per_page );

		// Same count as EDD Reviews' render_reviews().
		if ( function_exists( 'edd_reviews' ) && get_post_type( $post_id ) === 'download' ) {
			$reviews = edd_reviews()->query_reviews( [ 'post_id' => $post_id, 'count' => true ] );
			$pages   = max( $pages, (int) ceil( (int) $reviews / $per_page ) );
		}

		return max( 1, $pages );
	}

	/**
	 * Like WordPress' comments_template(), commenters see their own comments awaiting moderation, which can add a page.
	 *
	 * @return array User ID or email address of the current commenter.
	 */
	private function get_unapproved_authors() {
		if ( get_current_user_id() ) {
			return [ get_current_user_id() ];
		}

		$email = wp_get_unapproved_comment_author_email();

		return $email ? [ $email ] : [];
	}
}
