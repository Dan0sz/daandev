<?php
/**
 * The main archive template file for inner content.
 *
 * @package kadence
 */

namespace Kadence;

/**
 * Hook for Hero Section
 */
do_action( 'kadence_hero_header' );
?>
<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main" role="main">
			<?php
			/**
			 * Hook for anything before main content
			 */
			do_action( 'kadence_before_main_content' );
			//			if ( kadence()->show_in_content_title() ) {
			get_template_part( 'template-parts/content/archive_header' );
			//			}
			if ( have_posts() ) {
				?>
                <div id="archive-container" class="<?php echo esc_attr( implode( ' ', get_archive_container_classes() ) ); ?>"<?php echo( get_archive_infinite_attributes() ?
					" data-infinite-scroll='" . esc_attr( get_archive_infinite_attributes() ) . "'" : '' ); ?>>
					<?php
					$last_category_name = '';

					while ( have_posts() ) {
						the_post();

						/**
						 * Get categories of current post.
						 */
						$categories = get_the_terms( get_the_ID(), 'hs-docs-category' );

						if ( ! empty( $categories ) ) {
							$category      = $categories[ 0 ];
							$category_name = $category->name;

							if ( ! is_search() && $category_name !== $last_category_name ) {
								echo '<h2 class="archive-category-title">' . esc_html( $category_name ) . '</h2>';
								$last_category_name = $category_name;
							}
						}

						/**
						 * Hook in loop entry template.
						 */
						do_action( 'kadence_loop_entry' );
					}
					?>
                </div>
				<?php
				get_template_part( 'template-parts/content/pagination' );
			} else {
				get_template_part( 'template-parts/content/error' );
			}
			/**
			 * Hook for anything after main content
			 */
			do_action( 'kadence_after_main_content' );
			?>
        </main><!-- #main -->
		<?php
		get_sidebar();
		?>
    </div>
</div><!-- #primary -->
