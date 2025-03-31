<div class="grow flex flex-col p-3 lg:p-6 gap-3 lg:gap-6">
    <div class="grow">
        <div class="flex gap-2">
            <div class="grow font-brand font-bold !text-black text-xl md:text-2xl">
				<?php echo edd_get_download_name( get_the_ID() ); ?>
            </div>
			<?php if ( edd_has_variable_prices( get_the_ID() ) ) : ?>

			<?php endif; ?>
            <div class="flex items-baseline gap-1.5">
				<?php if ( edd_has_variable_prices( get_the_ID() ) ) : ?>
                    <div class="!text-black whitespace-nowrap font-brand">
						<?php echo __( 'From:' ); ?>
                    </div>
				<?php endif; ?>
				<?php $price = daan_get_lowest_price( get_the_ID() ); ?>
				<?php if ( ! empty( $price[ 'signup_fee' ] ) && $price[ 'signup_fee' ] < 0 ): ?>
                    <div class="whitespace-nowrap relative font-brand font-medium text-primary-900 leading-none lg:leading-none after:absolute after:h-0.5 after:left-0 after:right-0 after:top-1/2 after:-translate-y-1/2 after:bg-error-500 after:opacity-80 lg:text-lg">
						<?php echo edd_currency_symbol( edd_get_currency() ) . edd_format_amount( $price[ 'amount' ] ); ?>
                    </div>
                    <div class="font-brand font-bold text-primary-500 leading-none lg:leading-none text-xl lg:text-2xl whitespace-nowrap">
						<?php echo edd_currency_symbol( edd_get_currency() ) . edd_format_amount( $price[ 'amount' ] + $price[ 'signup_fee' ] ); ?>
                    </div>
				<?php elseif ( isset( $price[ 'amount' ] ) ): ?>
                    <div class="font-brand font-bold text-primary-500 leading-none lg:leading-none text-xl lg:text-2xl whitespace-nowrap">
						<?php echo edd_currency_symbol( edd_get_currency() ) . edd_format_amount( $price[ 'amount' ] ); ?>
                    </div>
				<?php else: ?>
                    <div class="font-brand font-bold text-primary-500 leading-none lg:leading-none text-xl lg:text-2xl">
						<?php _e( 'Free', 'daandev' ); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
        <div class="mt-2 !text-black">
			<?php echo daan_get_tagline( get_the_ID() ); ?>
        </div>
    </div>
    <button class="font-brand font-semibold transition disabled:opacity-50 focus:outline-none text-center editor-noclick bg-primary-500 hover:bg-primary-600 text-white inline-block rounded-lg lg:rounded-xl text-base lg:text-lg py-2 lg:py-2.5 px-4 lg:px-6 shadow-lg flex items-center justify-center gap-2 w-full">
        <svg class="fill-current w-[1em] h-[1em] inline-block shrink-0" viewBox="0 0 576 512">
            <path d="M0 24C0 10.7 10.7 0 24 0L69.5 0c22 0 41.5 12.8 50.6 32l411 0c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3l-288.5 0 5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5L488 336c13.3 0 24 10.7 24 24s-10.7 24-24 24l-288.3 0c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5L24 48C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"></path>
        </svg>
        <div class="inline-block"><?php echo __( 'Buy now', 'daandev' ); ?></div>
    </button>
</div>