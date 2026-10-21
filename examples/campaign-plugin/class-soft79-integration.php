<?php
/** Optional adapter for the separate campaign plugin. Not loaded by SOFT79. */
defined( 'ABSPATH' ) || exit;

final class Campaign_Soft79_Integration {
    const FORK_ID = 'incoog/soft79-wc-pricing-rules';
    const FORK_URL = 'https://github.com/incoog/soft79-wc-pricing-rules';

    private $is_enabled;
    private $is_campaign_managed;

    /**
     * @param callable $is_enabled Reads the campaign plugin's saved switch (default false).
     * @param callable $is_campaign_managed Reads actual persisted campaign ownership for a WC_Product.
     */
    public function __construct( callable $is_enabled, callable $is_campaign_managed ) {
        $this->is_enabled = $is_enabled;
        $this->is_campaign_managed = $is_campaign_managed;
        add_filter( 'soft79_wcpr_product_pricing_enabled', array( $this, 'filter_product' ), 10, 2 );
    }

    /** Runtime capability check, independent of plugin folder names or load order. */
    public static function runtime_state() {
        if ( function_exists( 'soft79_wcpr_get_integration_info' ) ) {
            $info = soft79_wcpr_get_integration_info();
            if ( is_array( $info ) && isset( $info['fork_id'], $info['api_version'], $info['features'] )
                && self::FORK_ID === $info['fork_id'] && 1 === $info['api_version']
                && is_array( $info['features'] ) && in_array( 'product_pricing_exclusion', $info['features'], true ) ) {
                return 'fork_active';
            }
            return 'unsupported_fork';
        }
        return function_exists( 'SOFT79_WCPR' ) ? 'original_active' : 'none';
    }

    /** Called on the campaign settings page only; does not load inactive plugins. */
    public static function admin_state() {
        $state = self::runtime_state();
        if ( 'none' !== $state ) {
            return $state;
        }
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $original_installed = false;
        foreach ( get_plugins() as $file => $plugin ) {
            if ( ! in_array( basename( $file ), array( 'soft79-wc-pricing-rules.php', 'soft79-wc-pricing-rules-pro.php' ), true ) ) {
                continue;
            }
            $headers = get_file_data( WP_PLUGIN_DIR . '/' . $file, array( 'fork_id' => 'SOFT79 Fork ID' ) );
            if ( isset( $headers['fork_id'] ) && self::FORK_ID === $headers['fork_id'] ) {
                return 'fork_inactive';
            }
            $original_installed = true;
        }
        return $original_installed ? 'original_inactive' : 'none';
    }

    public function filter_product( $allowed, $product ) {
        if ( ! $allowed || 'fork_active' !== self::runtime_state() || ! call_user_func( $this->is_enabled ) ) {
            return $allowed;
        }
        return call_user_func( $this->is_campaign_managed, $product ) ? false : $allowed;
    }

    /** The parent settings form owns nonce/capability checks and option persistence. */
    public static function render_setting( $field_name, $enabled ) {
        $state = self::admin_state();
        if ( 'fork_active' === $state ) {
            echo '<input type="hidden" name="' . esc_attr( $field_name ) . '" value="0">';
            echo '<label><input type="checkbox" name="' . esc_attr( $field_name ) . '" value="1" ' . checked( (bool) $enabled, true, false ) . '> ';
            echo esc_html( 'Vill du att SOFT79 ska inaktiveras för produkter som rabatteras av kampanjen?' ) . '</label>';
        } elseif ( in_array( $state, array( 'original_active', 'original_inactive' ), true ) ) {
            echo '<p>' . esc_html( 'Originalversionen av SOFT79 är installerad. Det finns en vidareutvecklad version som automatiskt kan undantas för produkter som rabatteras av kampanjen.' );
            echo ' <a href="' . esc_url( self::FORK_URL ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( 'Du hittar den här på GitHub.' ) . '</a></p>';
        } elseif ( 'fork_inactive' === $state ) {
            echo '<p>' . esc_html( 'SOFT79-forken är installerad men inaktiv. Aktivera den för att kunna välja integrationen.' ) . '</p>';
        } elseif ( 'unsupported_fork' === $state ) {
            echo '<p>' . esc_html( 'Den aktiva SOFT79-versionen har ett integrationsgränssnitt som inte stöds. Integrationen är avstängd.' ) . '</p>';
        }
    }
}
