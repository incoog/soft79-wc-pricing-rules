<?php
define( 'ABSPATH', __DIR__ . '/' );
error_reporting( E_ALL );
set_error_handler( static function ( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
function add_action( ...$args ) {}
function get_option( $key, $default = false ) { return $default; }
function is_admin() { return false; }
$original_first = isset( $argv[1] ) && $argv[1] === 'original-first';
if ( $original_first ) {
    final class SOFT79_WC_Pricing_Rules_Plugin {}
    function SOFT79_WCPR() {}
}
require dirname( __DIR__ ) . '/soft79-wc-pricing-rules.php';
if ( $original_first ) {
    if ( function_exists( 'soft79_wcpr_get_integration_info' ) ) throw new RuntimeException( 'Inactive duplicate fork must not advertise API' );
} else {
    SOFT79_WCPR()->controller_init();
    if ( SOFT79_WCPR()->controller !== null ) throw new RuntimeException( 'No controller without WooCommerce' );
}
echo 'PASS boot ' . ( $original_first ? 'original-first' : 'without-woocommerce' ) . "\n";
