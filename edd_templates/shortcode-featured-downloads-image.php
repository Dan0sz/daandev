<?php if ( $logo_light = daan_get_logo_light() ) : ?>
	<?php $brand = daan_get_brand(); ?>
    <div class="bg-linear-to-br <?php echo $brand[ 'from-light' ]; ?> <?php echo $brand[ 'to-dark' ]; ?>"></div>
    <div class="col-span-4 flex items-center bg-linear-to-br <?php echo $brand[ 'from-light' ]; ?> <?php echo $brand[ 'to-dark' ]; ?>">
        <div class="-ml-12 xl:-ml-14">
            <img decoding="async" src="<?php echo $logo_light; ?>" alt="" class="block !h-28">
        </div>
    </div>
<?php endif; ?>
