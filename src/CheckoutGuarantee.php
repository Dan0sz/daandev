<?php
/**
 * @package     Daan\Theme
 * @description Show the money back guarantee right below the Complete Order button.
 * @company     Daan.dev
 * @author      Daan van den Bergh
 * @copyright   2026 Daan van den Bergh
 */

namespace Daan\Theme;

class CheckoutGuarantee {
	/**
	 * Build class.
	 */
	public function __construct() {
		add_action( 'edd_purchase_form_after_submit', [ $this, 'render' ] );
	}

	/**
	 * @return void
	 */
	public function render() {
		?>
        <p class="daan-checkout-guarantee">
            <svg viewBox="0 0 512 512" aria-hidden="true">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"></path>
            </svg>
            <span><?php esc_html_e( '14 day money back guarantee', 'daandev' ); ?></span>
        </p>
		<?php
	}
}
