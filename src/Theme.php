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
		// None of this is needed in the admin.
		if ( is_admin() ) {
			return;
		}

		/**
		 * Scripts/styles
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'daandev_enqueue_scripts' ], 15 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_dashicons' ] );

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
		 * Add copy after Buy Now button.
		 */
		add_action( 'edd_purchase_link_end', [ $this, 'add_microcopy' ], 9 );

		/**
		 * Move and modify EDD Reviews
		 */
		if ( function_exists( 'edd_reviews' ) ) {
			remove_filter( 'the_content', [ edd_reviews(), 'load_frontend' ] );
			add_action( 'kadence_before_footer', [ $this, 'echo_reviews' ] );
		}

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
		$file_modified = filemtime( get_stylesheet_directory() . '/assets/dist/js/edd-add-to-cart.min.js' );

		wp_enqueue_script( 'daan-dev-add-to-cart', get_stylesheet_directory_uri() . '/assets/dist/js/edd-add-to-cart.min.js', [], $file_modified, [ 'defer' => true ] );

		// Parent theme styles
		wp_enqueue_style( 'kadence-theme', get_template_directory_uri() . '/style.css' );

		$file_modified = filemtime( get_stylesheet_directory() . '/assets/dist/css/output.css' );

		// Tailwind CSS compiled file
		wp_enqueue_style( 'daan-dev-tailwind', get_stylesheet_directory_uri() . '/assets/dist/css/output.css', [ 'kadence-theme' ], $file_modified );
	}

	/**
	 * @return void
	 */
	public function enqueue_dashicons() {
		wp_enqueue_style( 'dashicons' );
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
		remove_action( 'kadence_mobile_navigation', 'Kadence\mobile_navigation' );

		if ( ! edd_is_checkout() ) {
			add_action( 'kadence_primary_navigation', [ $this, 'primary_navigation' ] );
			add_action( 'kadence_mobile_navigation', [ $this, 'mobile_navigation' ] );
		}

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
                        <a href="<?php echo home_url( 'account' ); ?>" class="menu-link">
                            <svg class="fill-current w-[1em] h-[1em] block text-xl" viewBox="0 0 448 512">
                                <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304l-91.4 0z"></path>
                            </svg>
                        </a>
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
                    <li class="menu-item menu-item-type-custom menu-item-object-custom">
                        <a class="relative" href="<?php echo edd_get_cart_contents() ? edd_get_checkout_uri() : '#'; ?>">
                            <svg class="fill-current w-[1em] h-[1em] text-xl" viewBox="0 0 576 512">
                                <path d="M0 24C0 10.7 10.7 0 24 0L69.5 0c22 0 41.5 12.8 50.6 32l411 0c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3l-288.5 0 5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5L488 336c13.3 0 24 10.7 24 24s-10.7 24-24 24l-288.3 0c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5L24 48C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"></path>
                            </svg>
                            <div class="<?php echo edd_get_cart_contents() ? '' :
								'hidden'; ?> absolute rounded-full text-white bg-primary-500 w-3.5 h-3.5 flex items-center justify-center text-xs right-0 top-1">
                                <span class="cart-count"><?php echo edd_get_cart_quantity(); ?></span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </nav><!-- #site-navigation -->
		<?php
	}

	/**
	 * Mobile Navigation, copied from @see Kadence\mobile_navigation and modified.
	 */
	public function mobile_navigation() {
		?>
        <nav id="mobile-site-navigation" class="mobile-navigation drawer-navigation drawer-navigation-parent-toggle-<?php echo esc_attr(
			kadence()->option( 'mobile_navigation_parent_toggle' ) ? 'true' : 'false'
		); ?>" role="navigation" aria-label="<?php esc_attr_e( 'Primary Mobile Navigation', 'kadence' ); ?>">
			<?php kadence()->customizer_quick_link(); ?>
            <div class="mobile-menu-container drawer-menu-container">
				<?php
				if ( kadence()->is_mobile_nav_menu_active() ) {
					kadence()->display_mobile_nav_menu( [ 'menu_id' => 'mobile-menu', 'menu_class' => ( kadence()->option( 'mobile_navigation_collapse' ) ? 'menu has-collapse-sub-nav' : 'menu' ) ] );
				} elseif ( kadence()->is_primary_nav_menu_active() ) {
					kadence()->display_primary_nav_menu(
						[
							'menu_id'      => 'mobile-menu',
							'menu_class'   => ( kadence()->option( 'mobile_navigation_collapse' ) ? 'menu has-collapse-sub-nav' : 'menu' ),
							'show_toggles' => ( kadence()->option( 'mobile_navigation_collapse' ) ? true : false ),
							'sub_arrows'   => false,
							'mega_support' => apply_filters( 'kadence_mobile_allow_mega_support', true ),
						]
					);
				} else {
					kadence()->display_fallback_menu();
				}
				?>
            </div>
            <div class="mobile-menu-container drawer-menu-container">
                <ul id="daan-dev-mobile-menu" class="menu daan-dev-account has-collapse-sub-nav">
                    <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-account-mobile-menu">
                        <div class="drawer-nav-drop-wrap">
                            <a href="<?php echo home_url( 'account' ); ?>" class="menu-link">
								<?php echo __( 'Account', 'daandev' ); ?>
                            </a>
                            <button class="drawer-sub-toggle" data-toggle-duration="10" data-toggle-target="#daan-dev-mobile-menu .menu-item-account-mobile-menu > .sub-menu" aria-expanded="false">
                                <span class="screen-reader-text">Toggle child menu</span>
                                <span class="kadence-svg-iconset">
                                    <svg aria-hidden="true" class="kadence-svg-icon kadence-arrow-down-svg" fill="currentColor" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><title>Expand</title><path d="M5.293 9.707l6 6c0.391 0.391 1.024 0.391 1.414 0l6-6c0.391-0.391 0.391-1.024 0-1.414s-1.024-0.391-1.414 0l-5.293 5.293-5.293-5.293c-0.391-0.391-1.024-0.391-1.414 0s-0.391 1.024 0 1.414z"></path></svg>
                                </span>
                            </button>
                        </div>
						<?php if ( is_user_logged_in() ) : ?>
							<?php
							wp_nav_menu(
								[
									'theme_location'  => 'daan-account-menu',
									'menu_class'      => 'sub-menu',
									'menu_id'         => 'daan-dev-account-mobile-menu',
									'container_class' => 'daan-dev-account-menu',
									'container'       => 'ul',
									'mega_support'    => true,
									'addon_support'   => true,
								]
							);
							?>
						<?php endif; ?>
                    </li>
                    <li class="menu-item menu-item-type-post_type menu-item-object-page">
                        <a class="relative" href="<?php echo edd_get_cart_contents() ? edd_get_checkout_uri() : '#'; ?>">
							<?php echo __( 'Cart', 'daandev' ); ?>
                            <div class="<?php echo edd_get_cart_contents() ? '' : 'hidden'; ?> inline-block text-center rounded-full text-white bg-primary-500 w-3.5 h-3.5 text-xs">
                                <span class="cart-count"><?php echo edd_get_cart_quantity(); ?></span>
                            </div>
                        </a>
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
			remove_action( 'kadence_middle_footer', 'Kadence\middle_footer' );
			add_action( 'kadence_middle_footer', [ $this, 'show_checkout_footer' ] );
		}

		$is_download = is_singular( get_post_type() ) && get_post_type() === 'download';

		global $post;

		$content = '';

		if ( ! empty( $post ) ) {
			$content = $post->post_content;
		}

		if ( ( ! is_front_page() && ! $is_download && $content && ! has_shortcode( $content, 'daan-featured-downloads' ) ) || is_tax( 'hs-docs-category' ) || is_search() ) {
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
		if ( ( $query->is_post_type_archive( 'hs-docs-article' ) || is_tax( 'hs-docs-category' ) || is_search() ) && ! is_admin() && $query->is_main_query() ) {
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

	public function add_microcopy() {
		?>
        <div class="grid grid-cols-1 gap-1.5">
            <div class="inline-flex items-center gap-1">
                <svg class="fill-current w-[1em] h-[1em] text-secondary-500" viewBox="0 0 512 512">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"></path>
                </svg>
                <span><?php echo __( '14 day money back guarantee' ); ?></span>
            </div>
            <div>
                Find answers to all your
                <a href="<?php echo esc_html(
					home_url( 'docs/pre-sales/' )
				); ?>" class="font-brand font-semibold transition disabled:opacity-50 focus:outline-none text-center editor-noclick !text-primary-500 border-transparent text-base border-b">
                    pre-sale questions
                </a>
            </div>
        </div>
		<?php
	}
}
