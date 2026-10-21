<?php
require __DIR__ . '/bootstrap.php';
$scenario = isset( $argv[1] ) ? $argv[1] : 'none';
if ( in_array( $scenario, array( 'original_active', 'fork_active', 'unsupported_fork', 'other_fork' ), true ) ) {
    function SOFT79_WCPR() {}
}
if ( in_array( $scenario, array( 'fork_active', 'unsupported_fork', 'other_fork' ), true ) ) {
    function soft79_wcpr_get_integration_info() {
        return array(
            'fork_id' => $GLOBALS['scenario'] === 'other_fork' ? 'someone/else' : 'incoog/soft79-wc-pricing-rules',
            'api_version' => $GLOBALS['scenario'] === 'unsupported_fork' ? 2 : 1,
            'features' => array( 'product_pricing_exclusion' ),
        );
    }
}
if ( in_array( $scenario, array( 'original_inactive', 'fork_inactive' ), true ) ) {
    $file = 'soft79-wc-pricing-rules/soft79-wc-pricing-rules.php';
    $GLOBALS['installed_plugins'][$file] = array( 'Name' => 'SOFT79 Pricing Rules' );
    if ( $scenario === 'fork_inactive' ) $GLOBALS['fork_headers'][WP_PLUGIN_DIR . '/' . $file] = 'incoog/soft79-wc-pricing-rules';
}
require dirname( __DIR__ ) . '/examples/campaign-plugin/class-soft79-integration.php';
$expected = $scenario === 'other_fork' ? 'unsupported_fork' : $scenario;
expect( Campaign_Soft79_Integration::admin_state() === $expected, 'Correct state for ' . $scenario );
ob_start(); Campaign_Soft79_Integration::render_setting( 'campaign[soft79]', true ); $html = ob_get_clean();
expect( ( strpos( $html, 'type="checkbox"' ) !== false ) === ( $scenario === 'fork_active' ), 'Switch visibility for ' . $scenario );
if ( strpos( $scenario, 'original_' ) === 0 ) expect( strpos( $html, Campaign_Soft79_Integration::FORK_URL ) !== false, 'Original shows fork link' );
if ( $scenario === 'none' ) expect( $html === '', 'No SOFT79 produces no integration UI' );
$enabled = true;
$adapter = new Campaign_Soft79_Integration( static function () use ( &$enabled ) { return $enabled; }, static function () { return true; } );
$p = new WC_Product( 1 );
expect( $adapter->filter_product( true, $p ) === ( $scenario !== 'fork_active' ), 'Only supported fork can exclude product' );
$enabled = false;
expect( $adapter->filter_product( true, $p ) === true, 'Switch off is a no-op in ' . $scenario );
expect( $adapter->filter_product( false, $p ) === false, 'Never override another exclusion' );
echo 'PASS ' . $scenario . ': ' . $GLOBALS['assertions'] . " assertions\n";
