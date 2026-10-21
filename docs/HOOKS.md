# Product exclusion hook

`soft79_wcpr_product_pricing_enabled` is a WordPress filter hook. It lets site code or another extension exclude a product from SOFT79 pricing rules.

| Argument | Type | Meaning |
| --- | --- | --- |
| `$enabled` | bool | Whether SOFT79 pricing is allowed. Defaults to true |
| `$product` | WC_Product | The product or variation currently being evaluated |
| Return value | bool | Return false to exclude this product, otherwise preserve the incoming value |

Example, excluding one product ID:

```php
add_filter( 'soft79_wcpr_product_pricing_enabled', function ( $enabled, $product ) {
    if ( ! $enabled ) {
        return false;
    }
    return 123 !== $product->get_id();
}, 10, 2 );
```

The filter covers local and global rules, cart price calculations, cumulative quantity thresholds, one-unit offer prices, catalog price ranges, product tables and variation payloads. Excluded products do not count toward cumulative thresholds. Removing an exclusion allows ordinary rules to apply again; cart recalculation removes an earlier SOFT79 adjustment before evaluating the current rules.

The callback receives variation objects as well as parent products. To exclude all variations of a parent, check `get_parent_id()` explicitly. Callbacks should be inexpensive, preserve an incoming false and avoid saving products or changing prices. Without a subscriber, the hook does not exclude anything.

## Fork identification

`soft79_wcpr_get_integration_info()` provides the running implementation's identity, version and supported hooks. Existing public helper names and response keys remain unchanged for compatibility:

```php
array(
    'fork_id' => 'incoog/soft79-wc-pricing-rules',
    'api_version' => 1,
    'version' => '1.5.1',
    'features' => array( 'product_pricing_exclusion' ),
    'url' => 'https://github.com/incoog/soft79-wc-pricing-rules',
);
```

Check `function_exists()` after plugins have loaded before calling the helper. It is declared only when this fork owns the SOFT79 runtime. An installed but inactive copy does not advertise this support. The helper identifies capabilities; the filter hook performs the exclusion. There is no HTTP endpoint or REST service.
