<?php
// Deliberately small WP/WC doubles: these tests exercise the shipped PHP logic,
// not WordPress database integration or the live customer's theme.
define( 'ABSPATH', __DIR__ . '/' );
error_reporting( E_ALL );
set_error_handler( static function ( $severity, $message, $file, $line ) {
    throw new ErrorException( $message, 0, $severity, $file, $line );
} );
$GLOBALS['hooks'] = array();
$GLOBALS['posts'] = array();
$GLOBALS['meta'] = array();
$GLOBALS['options'] = array();
$GLOBALS['can_edit'] = true;
$GLOBALS['post'] = null;
function add_filter( $name, $callback, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][$name][$priority][] = array( $callback, $args ); }
function add_action( $name, $callback, $priority = 10, $args = 1 ) { add_filter( $name, $callback, $priority, $args ); }
function apply_filters( $name, $value, ...$args ) {
    $priorities = isset( $GLOBALS['hooks'][$name] ) ? $GLOBALS['hooks'][$name] : array();
    ksort( $priorities );
    foreach ( $priorities as $callbacks ) foreach ( $callbacks as $callback ) {
        $value = call_user_func_array( $callback[0], array_slice( array_merge( array( $value ), $args ), 0, $callback[1] ) );
    }
    return $value;
}
function do_action( $name, ...$args ) { apply_filters( $name, isset( $args[0] ) ? $args[0] : null, ...array_slice( $args, 1 ) ); }
function get_post( $id ) { return isset( $GLOBALS['posts'][$id] ) ? $GLOBALS['posts'][$id] : null; }
function get_post_meta( $id, $key, $single = true ) { return isset( $GLOBALS['meta'][$id][$key] ) ? $GLOBALS['meta'][$id][$key] : ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][$id][$key] = $value; }
function delete_post_meta( $id, $key ) { unset( $GLOBALS['meta'][$id][$key] ); }
function get_posts( $args ) { return array_values( array_filter( $GLOBALS['posts'], static function ( $p ) use ( $args ) { return $p->post_type === $args['post_type']; } ) ); }
function get_option( $key, $default = false ) { return isset( $GLOBALS['options'][$key] ) ? $GLOBALS['options'][$key] : $default; }
function is_admin() { return false; }
function is_singular( $type ) { return false; }
function get_the_terms( $id, $type ) { return isset( $GLOBALS['meta'][$id]['terms'] ) ? $GLOBALS['meta'][$id]['terms'] : false; }
function register_post_type( $type, $args ) {}
function _x( $text, $context, $domain = '' ) { return $text; }
function __( $text, $domain = '' ) { return $text; }
function _e( $text, $domain = '' ) { echo $text; }
function esc_attr_e( $text, $domain = '' ) { echo esc_attr( $text ); }
function absint( $value ) { return abs( (int) $value ); }
function wc_clean( $value ) { return is_array( $value ) ? array_map( 'wc_clean', $value ) : trim( strip_tags( (string) $value ) ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) ); }
function wp_get_current_user() { return (object) array( 'roles' => array( 'customer' ) ); }
function current_user_can( ...$args ) { return $GLOBALS['can_edit']; }
function wp_is_post_revision( $id ) { return false; }
function wp_is_post_autosave( $id ) { return false; }
function wp_verify_nonce( $value, $action ) { return $value === 'valid:' . $action; }
function wp_nonce_field( $action, $name, $referer = true ) { echo '<input name="' . $name . '" value="valid:' . $action . '">'; }
function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : ( is_string( $value ) ? stripslashes( $value ) : $value ); }
function wc_format_decimal( $value ) { return preg_replace( '/[^0-9.\-]/', '', str_replace( ',', '.', (string) $value ) ); }
function wc_format_localized_price( $value ) { return (string) $value; }
function wc_price( $value ) { return number_format( (float) $value, 2, '.', '' ); }
function wc_format_sale_price( $regular, $sale ) { return '<del>' . $regular . '</del><ins>' . $sale . '</ins>'; }
function wc_get_price_to_display( $product, $args ) { return $args['price']; }
function wc_get_price_including_tax( $product, $args ) { return $args['price'] * $args['qty'] * 1.25; }
function wc_get_price_excluding_tax( $product, $args ) { return $args['price'] * $args['qty']; }
function wc_prices_include_tax() { return false; }
function get_woocommerce_currency_symbol() { return 'kr'; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function wp_kses_post( $value ) { return preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $value ); }
function checked( $value, $current, $echo = true ) { $result = $value == $current ? 'checked="checked"' : ''; if ( $echo ) echo $result; return $result; }
function selected( $value, $current = true, $echo = true ) { $result = $value == $current ? 'selected="selected"' : ''; if ( $echo ) echo $result; return $result; }
function setup_postdata( $post ) {}
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function plugins_url( $path = '', $file = '' ) { return '/plugins/soft79-wc-pricing-rules/' . $path; }
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function locate_template( $name ) { return false; }
function wp_enqueue_script( ...$args ) {}
class WooCommerce {
    public $cart;
    public function __construct() { $this->cart = new Test_Cart(); }
    public function plugin_url() { return '/woocommerce'; }
}
class WC_Product {
    private $id;
    private $price;
    private $parent;
    public function __construct( $id, $price = 100, $parent = 0 ) {
        $this->id = $id; $this->price = (string) $price; $this->parent = $parent;
        $GLOBALS['posts'][$id] = (object) array( 'ID' => $id, 'post_type' => $parent ? 'product_variation' : 'product', 'post_content' => '' );
    }
    public function get_id() { return $this->id; }
    public function get_parent_id() { return $this->parent; }
    public function get_price() { return $this->price; }
    public function set_price( $value ) { $this->price = (string) $value; }
    public function get_price_suffix( $price = '' ) { return ''; }
    public function managing_stock() { return false; }
    public function backorders_allowed() { return false; }
    public function get_stock_quantity() { return 100; }
    public function is_type( $type ) { return false; }
}
class WC_Product_Variation extends WC_Product {}
class Test_Cart {
    public $items = array();
    public function get_cart() { return $this->items; }
    public function get_tax_price_display_mode() { return 'excl'; }
    public function get_total_tax() { return 0; }
}
function WC() { static $wc; if ( ! $wc ) $wc = new WooCommerce(); return $wc; }
$GLOBALS['assertions'] = 0;
function expect( $condition, $message ) { ++$GLOBALS['assertions']; if ( ! $condition ) throw new RuntimeException( $message ); }
function price_is( $product, $expected, $message ) { expect( abs( (float) $product->get_price() - $expected ) < 0.00001, $message . ': got ' . $product->get_price() ); }
function tier( $from, $to, $price ) { return array( 'qty_from' => $from, 'qty_to' => $to, 'price' => $price ); }
function set_rules( $id, $rules ) { $GLOBALS['meta'][$id]['_j79_bulk_rules'] = $rules; }
function controller() { $c = new SOFT79_Rule_Controller_PRO( SOFT79_WCPR()->options ); SOFT79_WCPR()->controller = $c; return $c; }
function cart_line( $product, $quantity ) { return array( 'data' => $product, 'quantity' => $quantity ); }
