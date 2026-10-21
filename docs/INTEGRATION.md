# Integration contract, API 1

## Detect the running implementation

After plugins have loaded, call `soft79_wcpr_get_integration_info()` only if it exists. The current response is:

```php
array(
    'fork_id' => 'incoog/soft79-wc-pricing-rules',
    'api_version' => 1,
    'version' => '1.5.0',
    'features' => array( 'product_pricing_exclusion' ),
    'url' => 'https://github.com/incoog/soft79-wc-pricing-rules',
);
```

Validate identity, supported API version and feature. Do not identify an active fork by its directory name, display name, version number, `has_filter()` or installed-but-inactive files. The API function is declared only if this fork owns the main SOFT79 runtime.

| Admin state | Integration UI | Pricing integration |
| --- | --- | --- |
| No SOFT79 | No SOFT79 controls | No-op |
| Original active or installed | Information explaining the compatible fork, with its GitHub link | No-op |
| Compatible fork installed but inactive | Activate-fork information | No-op |
| Compatible fork active | Optional switch | Saved switch, default off |
| Unrecognized fork/API version | Unsupported-version information | No-op |

The campaign plugin's own settings form owns permission checks, nonce verification, validation and persistence. The adapter only renders the field. It must read saved preferences on each request, never trust a checkbox from a frontend request. If the fork disappears, a remembered preference does not force an unavailable integration to run.

## Product gate

```php
add_filter( 'soft79_wcpr_product_pricing_enabled', function ( $enabled, $product ) {
    // Read the campaign plugin's saved switch and actual product ownership.
    // Return the incoming value for every product the campaign does not own.
    return $enabled;
}, 10, 2 );
```

Return `false` to prevent this product from receiving SOFT79 rules. Both product-local and global rules are gated, as are one-unit offer prices, catalog price HTML, product tables, variation payloads and direct price-range calls. Excluded products do not count toward global quantity thresholds for eligible products. The hook takes a real `WC_Product`, including `WC_Product_Variation` objects. The campaign's ownership resolver must consult variation IDs and parent campaign rules appropriately; a parent flag alone does not make every child a discounted item.

Callbacks must be cheap, deterministic for the current request, preserve an incoming `false`, and avoid changing prices or saving products. Their answer is not cached permanently. The cart engine restores only its own last temporary price before recalculating, so changing a gate or reducing quantities cannot retain a stale SOFT79 adjustment.

## Ownership and campaign state

Use a persisted campaign identifier and per-product state, not `is_on_sale()` or category membership alone. The campaign blocks SOFT79 only after its own price has been applied successfully and releases the block only after successful restoration. Manually changed sale prices retain the agreed SOFT79 protection through the remainder of the campaign. Handle interrupted processing and partial restoration per product.

The adapter constructor accepts two callbacks: one reads the saved switch, and one determines actual campaign ownership for a product. It registers the filter without depending on plugin load order. It is inert if the compatible fork is absent.

## Campaign requirements retained for the separate implementation

The campaign engine is not part of this fork. It will set ordinary WooCommerce sale prices, leave regular prices untouched and preserve original sale values and sale dates before writing. It supports a percentage, exclusion cutoff date, excluded categories including descendants, and parent-driven eligibility for variations.

When regular prices change, current campaign prices are recalculated. If a product had a prior sale price, changing its regular price creates a blocking conflict on the campaign settings page. The admin selects original sale amount or original discount percentage per product. A later regular-price change invalidates the earlier decision. Every blocking conflict must be resolved before a campaign-stop request is accepted on the server.

A manual sale-price change leaves automatic campaign pricing and appears in a separate list. Keeping the manual sale price is the default; removing the sale at campaign end is optional. Manual ownership supersedes an earlier blocking conflict. Original snapshots remain available for history. Expired original sale schedules must not be reactivated. Restore through resumable processing, never overwrite a newer admin change silently, and preserve regular-price edits made during the campaign.
