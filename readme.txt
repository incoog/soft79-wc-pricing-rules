=== SOFT79 Pricing Rules for WooCommerce (Community fork) ===
Contributors: josk79, incoog
Tags: woocommerce, pricing, discount
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily create bulk prices or discounts in WooCommerce.

== Description ==

Community maintenance fork of Soft79's original plugin. Existing settings, rule
post types, metadata, translations and theme template overrides are retained.
This fork adds a general WordPress filter hook for excluding individual
products. See README.md and docs/HOOKS.md for usage and maintained changes.

Original author: Soft79 (josk79). Fork maintenance: incoog.


"Pricing Rules for WooCommerce" allows you to set bulk pricing rules to products. For example 5% off if customer buys 6 or more. 10% off if customer buys 12 or more.

The prices will be presented to the customer on the product page in the form of a table.

Features:

Create pricing rules the easy way!

* Per-product bulk pricing
* Automatically presents a table with the available prices
* Bulk pricing rules for multiple products
* Bulk pricing rules for certain categories
* Bulk pricing rules for combinations of products
* Pricing rules based on customer roles
* Automatically presents a custom message to inform the customer about offers

More information on [www.soft79.nl](http://www.soft79.nl).

== Installation ==

1. Upload the plugin in the `/wp-content/plugins/` directory, or automatically install it through the 'New Plugin' menu in WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Can I change the way the discounted price is displayed? =

You can use the filter `'soft79_wcpr_min_max_price_html' ( $new_price_html, $original_price_html, $product, $min_price, $max_price, $is_singular )`

The following will display 'From $ x.xx' on the catalog page (Notice that the setting Show min-max price range on category page must be checked):

```php
add_filter( 'soft79_wcpr_min_max_price_html', 'soft79_custom_min_max_price_html', 10, 6 );
function soft79_custom_min_max_price_html( $new_price_html, $original_price_html, $product, $min_price, $max_price, $is_singular ) {
    if ( ! $is_singular ) {
        return sprintf( __( 'From %s', 'your-text-domain' ), wc_price( wc_get_price_to_display( $product, array( 'price' => $min_price ) ) ) );
    }
    return $new_price_html;
}
```


== Screenshots ==

1. Integrated to the edit product page
2. Optionally displays the from-to price range on the category page.
3. Optionally presents a table with available prices/discounts to the customer.

== Changelog ==

The comparison baseline is Soft79's final `master` commit, [`3d495d5a79a92da069df005a6157ca92a39a68e9`](https://github.com/soft79/soft79-wc-pricing-rules/commit/3d495d5a79a92da069df005a6157ca92a39a68e9), dated 2024-09-14 and labelled `1.4.5-dev.1` in the plugin header. Every fork change below is relative to that baseline. The original controller-options fix, PHP 8.2 dynamic-property fix and cart-tax display helper already existed there and are not new fork changes.

= 1.5.1, 2026-10-21 =

* Scope: Remove application-specific example code and its settings/detection tests. Keep this repository focused on SOFT79 itself
* Documentation: Describe the product exclusion mechanism as a WordPress filter hook; replace the integration guide with generic hook documentation
* Documentation: Add this complete changelog to both README.md and readme.txt, with the exact upstream comparison baseline
* Documentation: Remove installation-specific context from the public README and update the validation notes
* Documentation: Correct the price-display example to use current public WooCommerce functions
* Tests: Exercise the exclusion filter directly, including no-subscriber defaults, parent/variation handling and preserving an incoming false
* Compatibility: Preserve the filter name, fork-identification helper and existing response keys
* Metadata: Update the plugin description and version to 1.5.1

= 1.5.0, 2026-10-21 =

* Product exclusions: Add `soft79_wcpr_product_pricing_enabled`, defaulting to true for valid products
* Product exclusions: Apply the filter to local and global rule lookup, direct rule eligibility, price ranges, one-unit offers, product tables and variation data
* Product exclusions: Exclude filtered products from cumulative quantity thresholds
* Fork identification: Add a generic helper exposing the active fork identity, version and supported product exclusion capability; inactive duplicate copies do not advertise support
* Cart recalculation: Restore the previous non-SOFT79 price before each totals pass, then clear temporary adjustment/display state
* Cart recalculation: Remove stale discounts when quantities fall below a threshold or a product becomes excluded; avoid compounding the plugin's own discount
* Cart recalculation: Respect an externally changed base price instead of overwriting it with an older baseline
* Cart recalculation: Track temporary prices by product object so separate cart lines sharing a product ID retain their own base prices
* Cart recalculation: Guard against reentrant calculations, missing carts and malformed or zero-quantity cart lines
* Pricing: Limit finite quantity tiers to the units actually remaining on each order line
* Pricing: Validate stored rule shapes, positive lower quantity limits, upper quantity boundaries and numeric price values; ignore invalid rows without deleting them
* Pricing: Reject invalid or nonpositive accounted quantities and guard relative-price calculations against malformed values
* Pricing: Clamp calculated unit prices to zero so excessive negative adjustments cannot create negative charges
* Price display: Check product and user eligibility before building a price range; preserve incoming price HTML for excluded or invalid products
* Admin permissions: Use WooCommerce product capabilities for the global rule post type and require manage-WooCommerce permission when saving global rule metadata
* Admin save protection: Add dedicated nonce checks to product and variation rule saves; use an indexed nonce for each variation editor
* Admin save protection: Correct autosave/revision handling and unslash submitted rule data before processing
* Admin validation: Whitelist settings fields, validate boolean/enum values and support empty or malformed submissions safely
* Admin validation: Include the cart-subtotal display checkbox in normalized checkbox settings
* Admin validation: Normalize product/category ID lists, remove duplicates, reject nested values and sanitize user-role lists
* Admin validation: Validate quantity rows and decimal/percentage inputs, rejecting malformed or reversed ranges
* Admin preservation: Stop overwriting the unused sale-exclusion metadata field from an unrelated form field
* Admin rendering: Normalize an empty rule editor to an array before counting rows; escape localized price input values
* Admin rendering: Handle taxonomy lookup errors and replace invalid nested paragraph markup in variation editors
* Admin assets: Load styles/scripts only on relevant admin screens, declare the jQuery dependency and use the plugin version for asset caching
* Admin row controls: Insert new rule rows only into the clicked editor instead of every editor on the page
* Admin row controls: Trigger a change event after adding/removing rows so WooCommerce marks variation edits as unsaved
* Variation display: Scope table updates to the owning product, clear missing/empty variation data and restore the parent table when a selection resets
* Description rendering: Restore the surrounding global post context with a finally block, including when a content callback throws
* Description rendering: Only render global-rule descriptions, avoiding product/variation descriptions being processed as rule descriptions
* Templates: Use `EXTR_SKIP` when extracting template variables, escape quantity labels and sanitize rendered description/price HTML
* Compatibility: Use public cart tax methods and the WooCommerce tax configuration instead of deprecated cart properties; fall back to the tax-display option
* Compatibility: Use public product getters/setters instead of protected generic property methods or direct properties
* Compatibility: Fix the legacy PRO loader to require the correct plugin file once
* Defensive checks: Validate rule post objects and add direct-access guards to include/template files and the legacy loader
* Plugin identity: Add a distinct fork display name, author attribution, version, dependency headers, text-domain metadata, Update URI and identification headers
* Licensing: Add the GPL licence file and retain original authorship and licence terms
* Development examples: Initially add an optional extension adapter and isolated identification/UI fixtures; these are removed in 1.5.1
* Tests: Add PHP syntax, pricing/admin regression and boot checks, with warnings/deprecations promoted to exceptions in the fixtures
* Tests: Add ten jQuery/jsdom DOM checks for editor rows and variation table behavior; lock development dependency versions
* Automation: Add GitHub Actions checks for PHP 7.4, 8.2, 8.3, 8.4 and 8.5 plus Node 24 DOM tests; update checkout/setup-node actions to their Node 24 versions
* Maintenance: Add review/validation notes, development instructions and ignore rules for dependencies, build output and logs

== Original upstream changelog ==

= 1.4.5 =
*Release Date - ????-??-??*
* FIX: PHP 8.2: Dynamic Properties are deprecated

= 1.4.4 =
*Release Date - 2020-12-10*
* FIX: Deprecated WC_Cart->tax_display_cart()
* FIX: Options in PRO constructor

= 1.4.3 =
*Release Date - 2020-01-20*
* All PRO functionality is now included in the FREE version

= 1.4.2 =
*Release Date - 2020-01-19*
* FEATURE: WP-Multilang support
* FIX: Display sale price if a discount applies to 1x quantity
* FIX: PHP Notice about count() and bulk_rules

= 1.4.1 =
* FIX: Not able to exclude categories on global product rules

= 1.4.0 =
* FIX: Invalid discount calculated for tax exempt customers
* FIX: Product variation rules were not saved properly
* FIX: Javascript product variations

= 1.3.1 =
* FEATURE: Customizable package price format
* FIX: Admin Javascript in combination with newest WooCommerce version
* INTERNAL: Created SOFT79_Bulk_Pricing_Admin::update_post_bulk_rules

= 1.3.0 =
* INTERNAL: Filter 'soft79_wcpr_accounted_quantity' that allows overriding the amount of items that are taken in account for a discount.
* Removed 'sale price' logic for rules with a quantity of 1

= 1.2.0 =
* FEATURE: Bulk pricing rules for product variations

= 1.1.4 =
* FIX: Inject prices to cart items as soon as possible (fixes issue with coupon minimum spend).
* FIX: WPML Compatibility

= 1.1.3 =
* FIX: Discount on variations in WC3.0+
* FIX: PHP Deprecation message in class-meta-box-bulk-rules.php

= 1.1.2 =
* FIX: Removed debug messages "Not for this product/user"

= 1.1.1 =
* FIX: Product selector WooCommerce 3.0 Compatibility (Select2 v4)

= 1.1.0 =
* FIX: WooCommerce 3.0 Compatibility
* FEATURE: Overrideable template for the single page discount information
* PERFORMANCE: Use cached query when retrieving product categories.

= 1.0.4 =
* FEATURE: filter 'soft79_wcpr_min_max_price_html' to allow customization of the displayed min-max price range
* FIX: Compatibility with PHP versions prior to 5.5
* FIX: Variable product support

= 1.0.3.0 =
* FEATURE: Auto update price information when switching between product variants
* FIX: Removed some notices/warnings

= 1.0.2.4 =
* FIX: If multiple variants of the same product exist in the cart, the same price would be applied to all variants

= 1.0.2.3 =
* FIX: Fatal error if WooCommerce was disabled
* FIX: Respect 'woocommerce_tax_display_shop' and 'woocommerce_tax_display_cart'
* FIX: Respect price suffix and excl / incl tax message in cart subtotal

= 1.0.2.1 =
* FIX: Removed auto update code

= 1.0.2 =
* FIX: Some PHP warnings were displayed on fresh installations.
* FIX: Tax calculation

= 1.0.0 =
* First public release
