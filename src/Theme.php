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
		add_filter( 'wpseo_breadcrumb_output', [ $this, 'modify_yoast_breadcrumb_output' ], 10 );

		/**
		 * Remove after content Buy Block.
		 */
		remove_action( 'edd_after_download_content', 'edd_append_purchase_link' );

		/**
		 * Move and modify EDD Reviews
		 */
		remove_filter( 'the_content', [ edd_reviews(), 'load_frontend' ] );
		add_action( 'kadence_before_footer', [ $this, 'echo_reviews' ] );

		/**
		 * Additional templates
		 */
		add_action( 'kadence_before_footer', [ $this, 'maybe_add_usp_footer' ], 8 );
		add_action( 'kadence_before_footer', [ $this, 'maybe_add_author_footer' ], 9 );

		/**
		 * Shortcodes
		 */
		add_action( 'init', [ $this, 'add_reviews_shortcode' ], 11 );
		add_action( 'init', [ $this, 'add_featured_downloads_shortcode' ], 11 );

		/**
		 * Modify the Header/Footer in checkout.
		 */
		add_action( 'wp', [ $this, 'maybe_change_header' ] );
		add_action( 'wp', [ $this, 'maybe_change_footer' ] );

		/**
		 * Template hooks
		 */
		add_action( 'kadence_hero_header', [ $this, 'maybe_add_download_hero_header' ] );
	}

	/**
	 * Enqueue styles and Tailwind CSS
	 */
	public function daandev_enqueue_scripts() {
		$file_modified = filemtime( get_stylesheet_directory() . '/assets/js/edd-add-to-cart.min.js' );

		wp_enqueue_script( 'daan-dev-add-to-cart', get_stylesheet_directory_uri() . '/assets/js/edd-add-to-cart.min.js', [], $file_modified, [ 'defer' => true ] );

		// Parent theme styles
		wp_enqueue_style( 'kadence-theme', get_template_directory_uri() . '/style.css' );

		$file_modified = filemtime( get_stylesheet_directory() . '/assets/css/output.css' );

		// Tailwind CSS compiled file
		wp_enqueue_style( 'daan-dev-tailwind', get_stylesheet_directory_uri() . '/assets/css/output.css', [ 'kadence-theme' ], $file_modified );
	}

	/**
	 * @return void
	 */
	public function echo_reviews() {
		if ( ! function_exists( 'edd_reviews' ) || ! is_singular( get_post_type() ) || get_post_type() !== 'download' || edd_is_checkout() ) {
			return;
		}
		?>
        <div class="daan-dev-reviews bg-primary-50 py-12 lg:py-16">
            <div class="site-container">
				<?php echo edd_reviews()->load_frontend( '' ); ?>
            </div>
        </div>
		<?php
	}

	/**
	 * @return void
	 */
	public function maybe_add_usp_footer() {
		if ( ( is_singular( get_post_type() ) && get_post_type() === 'download' ) || edd_is_checkout() ) {
			get_template_part( 'template-parts/content/usp' );
		}
	}

	/**
	 * @return void
	 */
	public function maybe_add_author_footer() {
		if ( is_singular( get_post_type() ) && get_post_type() === 'download' ) {
			get_template_part( 'template-parts/content/author' );
		}
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
	 * Removes the navigation from the header, to make it distraction free.
	 *
	 * @return void
	 */
	public function maybe_change_header() {
		if ( edd_is_checkout() ) {
			remove_action( 'kadence_primary_navigation', 'Kadence\primary_navigation' );
			remove_action( 'kadence_secondary_navigation', 'Kadence\secondary_navigation' );
			remove_action( 'kadence_navigation_popup_toggle', 'Kadence\navigation_popup_toggle' );
		}
	}

	/**
	 * Loads an alternate, distraction free footer in checkout.
	 *
	 * @return void
	 */
	public function maybe_change_footer() {
		if ( edd_is_checkout() ) {
			//			remove_action( 'kadence_top_footer', 'Kadence\top_footer' );
			remove_action( 'kadence_middle_footer', 'Kadence\middle_footer' );
			add_action( 'kadence_middle_footer', [ $this, 'show_checkout_footer' ] );
		}

		$is_download = is_singular( get_post_type() ) && get_post_type() === 'download';

		global $post;

		$content = '';

		if ( ! empty( $post ) ) {
			$content = $post->post_content;
		}

		if ( ! is_front_page() && ! $is_download && $content && ! has_shortcode( $content, 'daan-featured-downloads' ) ) {
			remove_action( 'kadence_top_footer', 'Kadence\top_footer' );
		}
	}

	/**
	 * Add an additional check to @see edd_is_checkout()
	 *
	 * @param $value
	 *
	 * @return mixed|true
	 */
	public function filter_is_checkout( $value ) {
		if ( str_contains( $_SERVER[ 'REQUEST_URI' ], '/checkout' ) ) {
			return true;
		}

		return $value;
	}

	/**
	 * Load an alternate, distraction free footer.
	 *
	 * @return void
	 */
	public function show_checkout_footer() {
		get_template_part( 'template-parts/footer/checkout' );
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
	 * Add Tailwind classes to Yoast Breadcrumbs.
	 *
	 * @param $output
	 *
	 * @return string
	 */
	public function modify_yoast_breadcrumb_output( $output ) {
		return '<nav aria-label="Breadcrumb" class="text-xs leading-none mt-4 lg:mt-6 mb-8 lg:mb-12" vocab="https://schema.org/" typeof="BreadcrumbList">' . $output . '</nav>';
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
