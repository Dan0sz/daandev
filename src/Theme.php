<?php
/**
 * @package   Daan\Theme
 * @author    Daan van den Bergh
 * @copyright 2025 Daan van den Bergh
 * @license   MIT
 */

namespace Daan\Theme;

use function Kadence\kadence;

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
		 * Add Account Menu
		 */
		add_action( 'init', [ $this, 'add_account_menu' ] );

		/**
		 * Modify the Header/Footer in checkout.
		 */
		add_action( 'wp', [ $this, 'maybe_change_header' ] );
		add_action( 'wp', [ $this, 'maybe_change_footer' ] );

		/**
		 * Disable WP Help Scout Docs' default template, so we can use our own archive template.
		 */
		add_filter( 'wp_help_scout_docs_add_rewrite_rule', '__return_false' );
		add_action( 'pre_get_posts', [ $this, 'maybe_load_all_posts' ] );
		add_filter( 'kadence_title_elements_template_path', [ $this, 'maybe_disable_template_element' ], 10, 2 );

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
	 * Registers the Account menu position.
	 *
	 * @return void
	 */
	public function add_account_menu() {
		register_nav_menu( 'daan-account-menu', __( 'Account Menu', 'daandev' ) );
	}

	/**
	 * Removes the navigation from the header, to make it distraction free.
	 *
	 * @return void
	 */
	public function maybe_change_header() {
		remove_action( 'kadence_primary_navigation', 'Kadence\primary_navigation' );
		add_action( 'kadence_primary_navigation', [ $this, 'primary_navigation' ] );

		if ( edd_is_checkout() ) {
			remove_action( 'kadence_secondary_navigation', 'Kadence\secondary_navigation' );
			remove_action( 'kadence_navigation_popup_toggle', 'Kadence\navigation_popup_toggle' );
		}
	}

	/**
	 * Copied from @see \Kadence\primary_navigation() and modified to include an account menu and shopping cart.
	 *
	 * @return void
	 */
	public function primary_navigation() {
		/**
		 * Desktop Navigation
		 */
		$openType = get_theme_mod( 'primary_navigation_open_type' );
		?>
        <nav id="site-navigation" class="main-navigation header-navigation <?php echo $openType == 'click' ? 'click-to-open' : 'hover-to-open'; ?> nav--toggle-sub header-navigation-style-<?php echo esc_attr(
			kadence()->option( 'primary_navigation_style' )
		); ?> header-navigation-dropdown-animation-<?php echo esc_attr(
			kadence()->option( 'dropdown_navigation_reveal' )
		); ?>" role="navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'kadence' ); ?>">
			<?php
			kadence()->customizer_quick_link();
			?>
            <div class="primary-menu-container header-menu-container">
				<?php
				if ( kadence()->is_primary_nav_menu_active() ) {
					kadence()->display_primary_nav_menu( [ 'menu_id' => 'primary-menu' ] );
				} else {
					kadence()->display_fallback_menu();
				}
				?>
            </div>
            <div class="daandev-menu ml-6 flex items-center gap-2 md:gap-4">
                <ul id="daan-dev-menu" class="menu daan-dev-account">
                    <li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item--has-toggle">
                        <svg class="fill-current w-[1em] h-[1em] block text-xl" viewBox="0 0 448 512">
                            <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304l-91.4 0z"></path>
                        </svg>
						<?php if ( is_user_logged_in() ) : ?>
							<?php
							wp_nav_menu(
								[
									'theme_location'  => 'daan-account-menu',
									'menu_class'      => 'sub-menu',
									'menu_id'         => 'daan-dev-account-menu',
									'container_class' => 'daan-dev-account-menu',
									'container'       => 'ul',
									'mega_support'    => true,
									'addon_support'   => true,
								]
							);
							?>
						<?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav><!-- #site-navigation -->
		<?php
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

		if ( ( ! is_front_page() && ! $is_download && $content && ! has_shortcode( $content, 'daan-featured-downloads' ) ) || is_tax( 'hs-docs-category' ) ) {
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
	 * Load all posts if this is our Documentation page.
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function maybe_load_all_posts( $query ) {
		if ( ( $query->is_post_type_archive( 'hs-docs-article' ) || is_tax( 'hs-docs-category' ) ) && ! is_admin() && $query->is_main_query() ) {
			global $wpdb;

			// Run the custom SQL query
			$sql       = "SELECT p.* FROM {$wpdb->posts} AS p
                    INNER JOIN {$wpdb->term_relationships} AS r ON p.ID = r.object_id
                    INNER JOIN {$wpdb->term_taxonomy} AS tt ON r.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} AS t ON t.term_id = tt.term_id
                    WHERE tt.taxonomy = %s
                    AND p.post_status = 'publish'
                    AND p.post_type = %s
                    ORDER BY t.name ASC";
			$taxonomy  = 'hs-docs-category';
			$post_type = 'hs-docs-article';

			// Fetch posts based on the SQL query
			$posts    = $wpdb->get_results( $wpdb->prepare( $sql, $taxonomy, $post_type ) );
			$post_ids = wp_list_pluck( $posts, 'ID' );

			$query->set( 'post__in', $post_ids );
			$query->set( 'orderby', 'post__in' );
			$query->set( 'posts_per_page', - 1 );
		}

		return $query;
	}

	public function maybe_disable_template_element( $template, $item ) {
		if ( $item === 'title' && ( is_post_type_archive( 'hs-docs-article' ) || is_tax( 'hs-docs-category' ) ) ) {
			return '';
		}

		return $template;
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
