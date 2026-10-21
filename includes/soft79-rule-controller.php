<?php
defined( 'ABSPATH' ) || exit;


//The heart of the magic
class SOFT79_Rule_Controller {

    public $options = null;

    private $executing = false;

    public function __construct( $options = null ) {
        $this->options = $options;
    }

    public function is_pro() {
        return is_a( $this, 'SOFT79_Rule_Controller_PRO' );
    }

    public function get_valid_rules_for( $product ) {
        if ( ! SOFT79_Rule_Helpers::is_pricing_enabled( $product ) ) {
            return array();
        }
        $valid_rules = array();

        // For the free version the only valid rule would be the meta contained in the product or variations itself
        $valid_rules[] = new SOFT79_Bulk_Rule( SOFT79_Rule_Helpers::get_product_or_variation_id( $product ) );
        if ( SOFT79_Rule_Helpers::is_variation( $product ) ) {
            $valid_rules[] = new SOFT79_Bulk_Rule( SOFT79_Rule_Helpers::get_variable_product_id( $product ) );
        }
        return $valid_rules;
    }

    //We cheat by setting the product price to bulk price
    public function execute() {
        if ( $this->executing || ! WC()->cart ) {
            return;
        }
        $this->executing = true;
        try {
            // Each totals pass starts from the last non-SOFT79 price. Never compound
            // our own discount or retain it after a quantity change/exclusion.
            foreach ( $this->product_temp_data as $state ) {
                $product = $state['product'];
                if ( isset( $state['original_price'], $state['applied_price'] )
                    && (float) $product->get_price() === (float) $state['applied_price'] ) {
                    $product->set_price( $state['original_price'] );
                }
            }
            $this->product_temp_data = array();
            $cart_items = WC()->cart->get_cart();

            $discovered_rules = array();

            foreach ( $cart_items as $cart_item_key => $cart_item ) {
                if ( ! isset( $cart_item['data'], $cart_item['quantity'] ) || ! $cart_item['data'] instanceof WC_Product
                    || $cart_item['quantity'] <= 0 || ! is_numeric( $cart_item['data']->get_price() ) ) {
                    continue;
                }
                $product = $cart_item['data'];

                //Find best-value rule for this cart item
                $bestrule_total_discount = null;
                $bestrule_data = null ;
                foreach ( $this->get_valid_rules_for( $product ) as $rule ) {
                    //Acquaint the cart
                    if ( ! in_array( $rule, $discovered_rules, true ) ) {
                        $rule->discover_cart( $cart_items );
                        $discovered_rules[] = $rule;
                    }

                    $total_discount = $rule->get_discount_for_cart_item($cart_item, $data);
                    if ( $total_discount !== false && ( $total_discount < $bestrule_total_discount || $bestrule_total_discount === null ) ) {
                        $bestrule_total_discount = $total_discount;
                        $bestrule_data = $data;
                        if ( $this->options['rule_choice'] == 'first' ) { //first or best
                            break; //We apply the first rule, so immediately escape from this loop!
                        }
                    }
                }
                //Apply the best one, if found
                if ( $bestrule_data !== null ) {
                    //error_log("So the best price is $bestrule_avg_price " );
                    $this->set_temp_data( $product, 'stack_min_price', $bestrule_data['min'] );
                    $this->set_temp_data( $product, 'stack_max_price', $bestrule_data['max'] );
                    $this->set_product_bulk_price( $product, $bestrule_data['avg'] );
                    //error_log ( sprintf("Discounted price of %s on product %s", $bestrule_avg_price, $product->id) );
                }

            }
        } finally {
            $this->executing = false;
        }
    }

    /**
     *  If a discount applies for qty 1, get the discounted price. Otherwise returns false
     */
    public function get_sale_price( $product ) {
        $min_price = false;
        foreach ( $this->get_valid_rules_for( $product ) as $price_rule ) {
            $data = null;
            $discount = $price_rule->get_discount_for_product( $product, 1, $data ); //$data gets written to.
            if ( $discount !== false && ( $min_price === false || $data['avg'] < $min_price  ) ) {
                $min_price = $data['avg'];
            }
        }
        if ( $min_price !== false && $min_price < $product->get_price() ) {
            return  $min_price;
        }
        return false;
    }


// ===============================================================
// Apply temporary data to the products during cart calculations
// ===============================================================

    //All temporary data is stored here
    protected $product_temp_data = array();

    /**
     * Generate a key under which the temp data will be stored
     */
    protected function get_temp_key( $product ) {
        // Different cart lines may contain separate objects with the same ID
        // and different add-on prices. Keep their baselines separate.
        return spl_object_hash( $product );
    }

    /**
     *  Append temporary metadata to the product for during the cart calculation
     */
    public function set_temp_data( $product, $key, $value ) {
        $this->product_temp_data[ $this->get_temp_key( $product ) ]['product'] = $product;
        $this->product_temp_data[ $this->get_temp_key( $product ) ][$key] = $value;
    }

    /**
     *  Fetch temporary metadata from the product for during the cart calculation.
     *  Returns $default value if key not found
     */
    public function get_temp_data( $product, $key, $default = null ) {
        $temp_data_key = $this->get_temp_key( $product );
        if ( isset( $this->product_temp_data[$temp_data_key][$key] ) ) {
            return $this->product_temp_data[$temp_data_key][$key];
        } else {
            return $default;
        }
    }

    /**
     *  Set the product price to the bulk price
     */
    public function set_product_bulk_price( $product, $bulk_price ) {
        //If no price change, don't do anything. This will avoid having a from_to string with the same prices
        if ( $bulk_price != $product->get_price() ) {
            if ( $this->get_temp_data( $product, 'original_price' ) === null ) {
                $this->set_temp_data( $product, 'original_price', $product->get_price() );
            }
            $product->set_price( $bulk_price );
            $this->set_temp_data( $product, 'applied_price', $product->get_price() );
        }
    }

    public function is_pricing_rule_applied( $product ) {
        return $this->get_temp_data( $product, 'original_price' ) !== null;
    }

    public function get_original_price( $product ) {
        $orig_price = $this->get_temp_data( $product, 'original_price', $product->get_price() );
        //error_log("Orig: " . $orig_price);
        return $orig_price;
    }

}
