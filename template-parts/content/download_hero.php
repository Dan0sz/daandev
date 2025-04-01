<?php
/**
 * Template part for displaying a Download's Hero header
 *
 * @package kadence
 */

$classes   = [];
$classes[] = 'entry-header';
if ( is_singular( get_post_type() ) ) {
	$classes[] = get_post_type() . '-title';
}
$brand = daan_get_brand( get_the_ID() );
?>
<section role="banner" class="entry-hero <?php echo esc_attr( get_post_type() ) . '-hero-section'; ?>">
    <div class="entry-hero-container-inner site-container">
        <div class="hero-section-overlay alignwide"></div>
        <div class="-mx-4 xl:-mx-12 overflow-hidden rounded-xl md:rounded-3xl shadow-xl">
            <div class="grid grid-cols-5 md:grid-cols-12 aspect-3/1">
                <div class="md:col-span-2 bg-linear-to-br <?php echo $brand[ 'from-light' ]; ?> <?php echo $brand[ 'to-dark' ]; ?>"></div>
                <div class="col-span-4 md:col-span-10 py-10 pr-4 md:py-12 md:pr-8 lg:pr-4 flex bg-linear-to-br <?php echo $brand[ 'from-light' ]; ?> <?php echo $brand[ 'to-dark' ]; ?>">
                    <div class="flex items-center">
                        <div class="-ml-12 sm:-ml-16 lg:-ml-[5.75rem]">
                            <img src="<?php echo daan_get_logo_light( get_the_ID() ); ?>" class="block !h-24 sm:!h-32 lg:!h-46">
                            <div class="pl-[calc(6rem+(6rem/10.71))] sm:pl-[calc(8rem+(8rem/10.71))] lg:pl-[calc(11.5rem+(11.5rem/10.71))]">
                                <h1 class="font-brand text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-semibold !text-white">
									<?php echo daan_get_tagline( get_the_ID() ); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- .entry-hero -->
