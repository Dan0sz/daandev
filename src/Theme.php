<?php
/**
 * @package   Daan\Theme
 * @author    Daan van den Bergh
 * @copyright 2025 Daan van den Bergh
 * @license   MIT
 */

namespace Daan\Theme;

class Theme {
	/**
	 * Build class.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Action & filter hooks.
	 *
	 * @return void
	 */
	private function init() {
		/**
		 * Scripts/styles
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'daandev_enqueue_scripts' ], 15 );

		/**
		 * Modify blocks output
		 */
		add_action( 'after_setup_theme', [ $this, 'register_custom_latest_posts_image_size' ] );
		add_filter( 'register_block_type_args', [ $this, 'modify_latest_posts_callback' ], null, 2 );

		/**
		 * Shortcodes
		 */
		add_action( 'init', [ $this, 'add_reviews_shortcode' ], 11 );
		add_action( 'init', [ $this, 'add_featured_downloads_shortcode' ], 11 );

		/**
		 * Template hooks
		 */
		add_action( 'kadence_hero_header', 'maybe_add_download_hero_header' );
	}

	/**
	 * Enqueue styles and Tailwind CSS
	 */
	public function daandev_enqueue_scripts() {
		// Parent theme styles
		wp_enqueue_style( 'kadence-theme-css', get_template_directory_uri() . '/style.css' );

		$file_modified = filemtime( get_stylesheet_directory() . '/assets/css/output.css' );

		// Tailwind CSS compiled file
		wp_enqueue_style( 'daan-dev-tailwind-css', get_stylesheet_directory_uri() . '/assets/css/output.css', [ 'kadence-theme-css' ], $file_modified );
	}

	/**
	 * Add the reviews shortcode.
	 *
	 * @return void
	 */
	public function add_reviews_shortcode() {
		add_shortcode( 'reviews', [ new Reviews(), 'render' ] );
	}

	/**
	 * Add a custom daan-featured-downloads block, which renders Featured Downloads, added by the EDD Featured Downloads plugin.
	 *
	 * @return void
	 */
	public function add_featured_downloads_shortcode() {
		add_shortcode( 'daan-featured-downloads', [ new FeaturedDownloads(), 'render' ] );
	}

	/**
	 * Register our custom image size for the Latest Posts block.
	 *
	 * @return void
	 */
	public function register_custom_latest_posts_image_size() {
		add_image_size( 'daan-dev-latest-posts', 400, 250, true );
	}

	/**
	 * Modify the Latest Posts block to use our own callback. @see LatestPosts::render()
	 *
	 * @param $settings
	 * @param $name
	 *
	 * @return mixed
	 */
	public function modify_latest_posts_callback( $settings, $name ) {
		if ( $name == 'core/latest-posts' ) {
			$settings[ 'render_callback' ] = [ new LatestPosts(), 'render' ];
		}

		return $settings;
	}

	/**
	 * Adds the Hero image if current post is a Download.
	 *
	 * @return void
	 */
	public function maybe_add_download_hero_header() {
		if ( is_singular( get_post_type() ) && get_post_type() === 'download' ) {
			get_template_part( 'template-parts/content/download_hero' );
		}
	}
}
