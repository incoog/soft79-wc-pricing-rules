<?php
require __DIR__ . '/bootstrap.php';
require dirname( __DIR__ ) . '/soft79-wc-pricing-rules.php';
require dirname( __DIR__ ) . '/examples/campaign-plugin/class-soft79-integration.php';
$c = controller();
expect( $c->options['rule_choice'] === 'best', 'PRO controller receives settings' );
expect( soft79_wcpr_get_integration_info()['api_version'] === 1, 'Fork exposes API v1' );

$a = new WC_Product( 1, 100 );
set_rules( 1, array( tier( 3, 0, '-10%' ) ) );
WC()->cart->items = array( cart_line( $a, 3 ) );
$c->execute(); price_is( $a, 90, 'Quantity discount applies' );
$c->execute(); price_is( $a, 90, 'Repeated totals do not compound discount' );
WC()->cart->items[0]['quantity'] = 1;
$c->execute(); price_is( $a, 100, 'Reducing quantity removes previous discount' );
expect( ! $c->is_pricing_rule_applied( $a ), 'Removed discount clears display state' );
WC()->cart->items[0]['quantity'] = 3;
$c->execute();
$a->set_price( 150 );
$c->execute(); price_is( $a, 135, 'External price change replaces old baseline' );
$c->execute(); price_is( $a, 135, 'External baseline stays stable' );

$same_id = new WC_Product( 1, 200 );
WC()->cart->items = array( cart_line( $a, 3 ), cart_line( $same_id, 3 ) );
$c->execute(); price_is( $same_id, 180, 'Separate cart line of same product retains own price' );
price_is( $a, 135, 'First cart line retains own baseline' );

$enabled = false; $managed = array( 1 );
$adapter = new Campaign_Soft79_Integration(
    static function () use ( &$enabled ) { return $enabled; },
    static function ( $p ) use ( &$managed ) { return in_array( $p->get_id(), $managed, true ) || in_array( $p->get_parent_id(), $managed, true ); }
);
expect( Campaign_Soft79_Integration::runtime_state() === 'fork_active', 'Active fork recognized by API' );
expect( SOFT79_Rule_Helpers::is_pricing_enabled( $a ), 'Switch off preserves SOFT79' );
$enabled = true;
$c->execute(); price_is( $a, 150, 'Switch on removes already applied SOFT79 discount' );
price_is( $same_id, 200, 'Same product excluded on all cart lines' );
expect( $c->get_valid_rules_for( $a ) === array(), 'Exclusion gates product and global rules' );
expect( SOFT79_WCPR()->price_html( 'campaign-price', $a ) === 'campaign-price', 'Exclusion leaves campaign price HTML unchanged' );
expect( $c->get_sale_price( $a ) === false, 'Exclusion gates one-unit offer display' );
$rule = new SOFT79_Bulk_Rule( 1 );
expect( $rule->get_price_range( $a ) === array(), 'Direct range calculation also gated' );
ob_start(); $rule->render_product_page_html( $a ); $html = ob_get_clean();
expect( $html === '', 'Direct table rendering also gated' );
$variation = new WC_Product_Variation( 2, 80, 1 );
set_rules( 2, array( tier( 1, 0, '-20%' ) ) );
expect( $c->get_valid_rules_for( $variation ) === array(), 'Adapter handles parent exclusion for variations' );
$data = SOFT79_WCPR()->action_woocommerce_available_variation( array(), $a, $variation );
expect( $data['j79_soft79_single_product_discount'] === '', 'Variation AJAX table is empty when excluded' );
$enabled = false; $c->execute(); price_is( $a, 135, 'Switch off restores normal rule calculation' );

// Global quantity rules must not count campaign products toward thresholds.
$enabled = true;
$b = new WC_Product( 3, 100 );
$GLOBALS['posts'][99] = (object) array( 'ID' => 99, 'post_type' => 'j79_wc_price_rule', 'post_content' => 'Offer' );
$GLOBALS['meta'][99] = array( '_j79_rule_type' => 'bulk', '_j79_quantity_scope' => 'global', '_j79_bulk_rules' => array( tier( 3, 0, '-10%' ) ) );
$a->set_price( 80 ); $c = controller();
WC()->cart->items = array( cart_line( $a, 2 ), cart_line( $b, 1 ) );
$c->execute(); price_is( $a, 80, 'Campaign price is untouched in mixed cart' );
price_is( $b, 100, 'Excluded quantity does not unlock global discount' );
WC()->cart->items[1]['quantity'] = 3;
$c->execute(); price_is( $b, 90, 'Eligible quantity still unlocks discount' );
$c->execute(); price_is( $b, 90, 'Global discount is stable across repeated totals' );

// Finite tier: global quantity five, but this order line has only one unit.
$GLOBALS['meta'][99]['_j79_bulk_rules'] = array( tier( 3, 5, '-10%' ) );
$d = new WC_Product( 4, 100 ); $c = controller();
WC()->cart->items = array( cart_line( $b, 1 ), cart_line( $d, 4 ) );
$b->set_price( 100 ); $c->execute();
price_is( $b, 90, 'Finite tier never prices more units than exist on order line' );
price_is( $d, 90, 'Finite tier works across different eligible products' );

// First/best selection, packs, invalid legacy rows and a 100% discount.
unset( $GLOBALS['posts'][99], $GLOBALS['meta'][99] );
$enabled = false; $c = controller();
set_rules( 1, array( tier( 3, 3, '80' ) ) ); $a->set_price( 100 );
WC()->cart->items = array( cart_line( $a, 4 ) ); $c->execute();
price_is( $a, 85, 'Pack pricing leaves remaining units at base price' );
set_rules( 1, array( tier( 0, 0, '-20%' ), array( 'price' => array() ), tier( 1, 0, '0%' ) ) );
$c->execute(); price_is( $a, 0, 'Malformed stored rows ignored; zero price is valid' );
expect( SOFT79_Rule_Helpers::get_relative_price( 100, '-150%' ) == 0, 'Discount cannot produce negative charge' );
expect( SOFT79_Rule_Helpers::get_relative_price( 100, 'bad%' ) === 100, 'Malformed relative price keeps original' );
expect( SOFT79_Rule_Helpers::get_relative_price( 100, '-10' ) === 90.0, 'Fixed amount discount remains supported' );
expect( SOFT79_Rule_Helpers::get_product_prop( $a, 'id' ) === 1, 'Public product getter used' );
expect( ! SOFT79_Rule_Helpers::is_pricing_enabled( null ), 'Invalid product is safely ignored' );
WC()->cart = null; $c->execute(); WC()->cart = new Test_Cart();

$admin = new SOFT79_Bulk_Pricing_Admin();
expect( $admin->get_bulk_rules_from_form( null ) === null, 'Missing form is safe' );
expect( $admin->get_bulk_rules_from_form( array( 'price' => 'bad' ) ) === null, 'Malformed form shape is safe' );
$form = array( 'qty_from' => array( 3, 1, 0, 5, 1 ), 'qty_to' => array( 0, 0, 0, 2, 0 ), 'price' => array( '-10%', '99,50', '-5%', '50', array() ) );
$parsed = $admin->get_bulk_rules_from_form( $form );
expect( count( $parsed ) === 2 && $parsed[0]['price'] === '99.50', 'Form normalizes decimals and rejects invalid/nested rows' );
expect( $parsed[1]['price'] === '-10%', 'Form preserves percentage syntax' );
$opts = $admin->validate_options( array( 'rule_choice' => array(), 'pack_price_format' => array(), 'unexpected' => '<script>', 'show_cart_subtotal_as_from_to' => '1' ) );
expect( $opts['rule_choice'] === 'best' && $opts['pack_price_format'] === '0', 'Settings validate enum types' );
expect( ! isset( $opts['unexpected'] ) && $opts['show_cart_subtotal_as_from_to'] === '1', 'Settings whitelist fields and include subtotal checkbox' );
expect( $admin->validate_options( null )['show_min_max_price'] === '0', 'Empty settings submission is safe' );
expect( SOFT79_Bulk_Pricing_Admin::comma_separated_int_array( array( 1, 1, 0, array(), 2 ) ) === '1,2', 'Nested IDs ignored and duplicates removed' );
set_rules( 1, array( tier( 1, 0, 'old' ) ) );
$_POST = array( 'post_ID' => 1, '_j79_rules' => array( 'qty_from' => array( 1 ), 'qty_to' => array( 0 ), 'price' => array( '50' ) ) );
$admin->action_save_post( 1, get_post( 1 ) );
expect( get_post_meta( 1, '_j79_bulk_rules' )[0]['price'] === 'old', 'Product save requires nonce' );
$_POST['_j79_rules_nonce'] = 'valid:soft79_save_product_rules';
$admin->action_save_post( 1, get_post( 1 ) );
expect( get_post_meta( 1, '_j79_bulk_rules' )[0]['price'] === '50', 'Authorized product rule save works' );
$GLOBALS['can_edit'] = false; $_POST['_j79_rules']['price'][0] = '1';
$admin->action_save_post( 1, get_post( 1 ) );
expect( get_post_meta( 1, '_j79_bulk_rules' )[0]['price'] === '50', 'Product save requires capability' );
$GLOBALS['can_edit'] = true;
$_POST['_j79_variable_rules'] = array( 0 => $_POST['_j79_rules'] );
$_POST['_j79_variable_nonce'] = array( 0 => 'valid:soft79_save_product_rules' );
$admin->action_save_product_variation( 2, 0 );
expect( get_post_meta( 2, '_j79_bulk_rules' )[0]['price'] === '1', 'Authorized variation save works' );
unset( $_POST['_j79_rules_nonce'], $_POST['_j79_variable_nonce'] ); $_POST['_j79_variable_rules'][0]['price'][0] = '2';
$admin->action_save_product_variation( 2, 0 );
expect( get_post_meta( 2, '_j79_bulk_rules' )[0]['price'] === '1', 'Variation save requires nonce' );
ob_start(); $admin->render_admin_bulk_rules( null ); $html = ob_get_clean();
expect( substr_count( $html, 'soft79_bulk_row' ) === 4, 'Empty rule editor renders without null count errors' );

// Keep the original first/best choice, role/category restrictions and inheritance.
$p = new WC_Product( 6, 100 ); set_rules( 6, array( tier( 1, 0, '-10%' ) ) );
$GLOBALS['posts'][100] = (object) array( 'ID' => 100, 'post_type' => 'j79_wc_price_rule', 'post_content' => 'Offer' );
$GLOBALS['meta'][100] = array( '_j79_rule_type' => 'bulk', '_j79_bulk_rules' => array( tier( 1, 0, '80' ) ) );
$c = controller(); $c->options['rule_choice'] = 'first'; WC()->cart->items = array( cart_line( $p, 1 ) );
$c->execute(); price_is( $p, 90, 'First setting selects first applicable rule' );
$c->options['rule_choice'] = 'best'; $c->execute(); price_is( $p, 80, 'Best setting selects largest discount' );
$GLOBALS['meta'][100]['_j79_user_roles'] = array( 'administrator' ); $p->set_price( 100 ); $c = controller();
$c->execute(); price_is( $p, 90, 'Global rule still honors role restrictions' );
$GLOBALS['meta'][100]['_j79_user_roles'] = array(); $GLOBALS['meta'][100]['_j79_product_categories'] = array( 12 );
$p->set_price( 100 ); $c = controller(); $c->execute(); price_is( $p, 90, 'Global rule still honors category restrictions' );
$GLOBALS['meta'][6]['terms'] = array( (object) array( 'term_id' => 12 ) );
$c->execute(); price_is( $p, 80, 'Matching category enables global rule' );
$GLOBALS['meta'][100]['_j79_exclude_product_ids'] = '6'; $p->set_price( 100 ); $c = controller();
$c->execute(); price_is( $p, 90, 'Existing per-rule product exclusion still works' );
unset( $GLOBALS['posts'][100], $GLOBALS['meta'][100] );
$parent = new WC_Product( 20, 100 ); $child = new WC_Product_Variation( 21, 100, 20 );
set_rules( 20, array( tier( 2, 0, '-10%' ) ) );
$c = controller(); WC()->cart->items = array( cart_line( $child, 2 ) );
$c->execute(); price_is( $child, 90, 'Variation inherits parent rules' );
set_rules( 21, array( tier( 2, 0, '-20%' ) ) );
$c->execute(); price_is( $child, 80, 'Variation-specific rule can offer better price' );

$GLOBALS['post'] = get_post( 3 ); $before = $GLOBALS['post'];
$rule = new SOFT79_Bulk_Rule( 1 ); $rule->get_description();
expect( $GLOBALS['post'] === $before, 'Description restores surrounding loop post' );
add_filter( 'the_content', static function () { throw new RuntimeException( 'shortcode error' ); } );
try { $rule->get_description(); } catch ( RuntimeException $e ) {}
expect( $GLOBALS['post'] === $before, 'Description restores loop even if content callback throws' );
ob_start(); Campaign_Soft79_Integration::render_setting( 'campaign[soft79]', false ); $html = ob_get_clean();
expect( strpos( $html, 'type="checkbox"' ) !== false && strpos( $html, 'checked="checked"' ) === false, 'Switch appears only for supported active fork and defaults off' );
add_filter( 'soft79_wcpr_product_pricing_enabled', static function () { return false; }, 5 );
expect( apply_filters( 'soft79_wcpr_product_pricing_enabled', true, $a ) === false, 'Adapter preserves exclusions from other integrations' );
echo 'PASS ' . $GLOBALS['assertions'] . ' assertions; PHP ' . PHP_VERSION . "\n";
