<?php
/**
 * A testimonial template, this uses @package daan/featured-downloads-branding's Testimonial options to display the messages.
 */
$author            = get_post_meta( get_the_ID(), '_edd_featured_download_author', true );
$author_image      = get_post_meta( get_the_ID(), '_edd_featured_download_author_image', true );
$testimonial_title = get_post_meta( get_the_ID(), '_edd_featured_download_testimonial_title', true );
$testimonial       = get_post_meta( get_the_ID(), '_edd_featured_download_testimonial', true );

if ( empty( $author ) ) {
	$author = get_the_author_meta( 'display_name' ) . '<span class="font-normal">' . ' - ' . __( 'Fixing the internet, since 2010.', 'daandev' ) . '</span>';
}

if ( empty( $author_image ) ) {
	$author_image = get_avatar_url( get_the_author_meta( 'ID' ), [ 'size' => 400 ] );
}

if ( empty( $testimonial_title ) ) {
	$testimonial_title = sprintf( __( 'Hi there! 👋 I\'m %s; the plugin\'s creator.', 'daandev' ), get_the_author_meta( 'first_name' ) );
}

if ( empty( $testimonial ) ) {
	$testimonial = __(
		'Performance and UX are key when I craft a plugin. I create my plugins with care and always bring something new to the table. Reinventing the wheel isn\'t my style. I offer friendly, human support and there\'s never been a support ticket I wasn\'t able to resolve.',
		'daandev'
	);
}

?>
<div class="daan-dev-usp py-12 lg:py-16">
    <div class="site-container container">
        <blockquote class="about-the-author text-black text-center py-0 px-10 lg:px-28 border-l-0">
            <div class=" flex flex-col lg:flex-row gap-y-4 items-center align-center lg:gap-x-18">
                <div class="rounded-full w-20 md:w-32 lg:w-48 shadow-md shrink-0 relative overflow-hidden">
                    <figure data-loading="lazy" class="relative aspect-1/1 !m-0">
                        <img alt="<?php echo strip_tags(
							$author
						); ?>" class="max-w-none w-full h-full absolute inset-0 object-cover opacity-0 transition duration-75 ease-in opacity-100" width="400" height="400" src="<?php echo esc_html(
							$author_image
						); ?>">
                    </figure>
                </div>
                <div class="!text-left">
                    <p class="inline-block relative z-10 text-xl lg:text-3xl font-brand !font-semibold">
                        <svg class="fill-current w-[1em] h-[1em] absolute -left-6 lg:-left-10 z-0 -top-6 lg:-top-10 h-auto text-primary-100 text-6xl lg:text-8xl" viewBox="0 0 448 512">
                            <path d="M0 216C0 149.7 53.7 96 120 96l8 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-8 0c-30.9 0-56 25.1-56 56l0 8 64 0c35.3 0 64 28.7 64 64l0 64c0 35.3-28.7 64-64 64l-64 0c-35.3 0-64-28.7-64-64l0-32 0-32 0-72zm256 0c0-66.3 53.7-120 120-120l8 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-8 0c-30.9 0-56 25.1-56 56l0 8 64 0c35.3 0 64 28.7 64 64l0 64c0 35.3-28.7 64-64 64l-64 0c-35.3 0-64-28.7-64-64l0-32 0-32 0-72z"></path>
                        </svg>
                        <span class="z-10 relative"><?php echo esc_html( $testimonial_title ); ?></span>
                    </p>
                    <p class="mt-2">
                        <span class="z-10 relative"><?php echo wp_kses( $testimonial, 'post' ); ?></span>
                    </p>
                    <div class="text-sm lg:text-base font-bold mt-2 lg:mt-4">
                        <span><?php echo wp_kses( $author, 'post' ); ?></span>
                    </div>
                </div>
            </div>
        </blockquote>
    </div>
</div>