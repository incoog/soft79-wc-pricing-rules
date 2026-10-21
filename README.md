# SOFT79 Pricing Rules for WooCommerce

An independently maintained update of [Soft79's original plugin](https://github.com/soft79/soft79-wc-pricing-rules), maintained by [incoog](https://github.com/incoog/soft79-wc-pricing-rules). Define quantity-based prices for products, variations, categories or combinations of products, with optional user-role restrictions and product-page price tables.

Existing pricing rules, settings, metadata keys, translations, template override paths and CSS classes are retained. No other pricing extension is required.

## Requirements and installation

WordPress 6.5+, WooCommerce 8.0+ and PHP 7.4+. These are support boundaries, not a claim that every combination has been tested end to end.

Download the repository ZIP using **Code → Download ZIP**. No build step or development dependencies are required. When replacing an existing installation, replace the files inside `wp-content/plugins/soft79-wc-pricing-rules/` and retain that directory name. Existing rule data stays in place; no migration or price write runs on installation.

The plugin basename is retained for replacing an existing installation. The display name identifies this fork and its `Update URI` prevents an unrelated WordPress.org update from overwriting it. Automatic GitHub updates are not implemented.

## Changelog

The comparison baseline is Soft79's final `master` commit, [`3d495d5a79a92da069df005a6157ca92a39a68e9`](https://github.com/soft79/soft79-wc-pricing-rules/commit/3d495d5a79a92da069df005a6157ca92a39a68e9), dated 2024-09-14 and labelled `1.4.5-dev.1` in the plugin header. Every fork change below is relative to that baseline. The original controller-options fix, PHP 8.2 dynamic-property fix and cart-tax display helper already existed there and are not new fork changes.

### 1.5.2, 2026-10-21

| Type | Area | Change |
| --- | --- | --- |
| Removed | Repository cleanup | Remove `docs`, automated test files, the GitHub Actions test workflow, npm manifests/lockfile and test-tool ignore rules |
| Changed | Documentation | Keep the reviewed fixes and short descriptions of the product exclusion hook and fork-identification function in this changelog; label fixes as Fixed and new features as Added |
| Changed | Installation | Document direct ZIP download and installation without development dependencies |
| Changed | Verification | Retain a short account of completed checks; future changes are verified manually on staging |
| Changed | Metadata | Update the plugin version to 1.5.2; pricing behavior is unchanged |

### 1.5.1, 2026-10-21

| Type | Area | Change |
| --- | --- | --- |
| Removed | Scope | Remove application-specific example code and its settings/detection tests. Keep this repository focused on SOFT79 itself |
| Changed | Documentation | Describe the product exclusion mechanism as a WordPress filter hook; replace the integration guide with generic hook documentation |
| Added | Documentation | Add this complete changelog to both README.md and readme.txt, with the exact upstream comparison baseline |
| Removed | Documentation | Remove installation-specific context from the public README and update the validation notes |
| Fixed | Documentation | Correct the price-display example to use current public WooCommerce functions |
| Added | Tests | Exercise the exclusion filter directly, including no-subscriber defaults, parent/variation handling and preserving an incoming false |
| Changed | Compatibility | Preserve the filter name, fork-identification helper and existing response keys |
| Changed | Metadata | Update the plugin description and version to 1.5.1 |

### 1.5.0, 2026-10-21

| Type | Area | Change |
| --- | --- | --- |
| Added | Product exclusions | Add the WordPress filter hook `soft79_wcpr_product_pricing_enabled` (`$enabled`, `$product`); return false to exclude a product or variation, otherwise preserve the incoming value. Defaults to true |
| Added | Product exclusions | Apply the filter to local and global rule lookup, direct rule eligibility, price ranges, one-unit offers, product tables and variation data |
| Added | Product exclusions | Exclude filtered products from cumulative quantity thresholds |
| Added | Fork identification | Add `soft79_wcpr_get_integration_info()` to expose the active fork ID, version and supported hooks. Check `function_exists()` after plugins load; inactive duplicate copies do not advertise support |
| Fixed | Cart recalculation | Restore the previous non-SOFT79 price before each totals pass, then clear temporary adjustment/display state |
| Fixed | Cart recalculation | Remove stale discounts when quantities fall below a threshold or a product becomes excluded; avoid compounding the plugin's own discount |
| Fixed | Cart recalculation | Respect an externally changed base price instead of overwriting it with an older baseline |
| Fixed | Cart recalculation | Track temporary prices by product object so separate cart lines sharing a product ID retain their own base prices |
| Fixed | Cart recalculation | Guard against reentrant calculations, missing carts and malformed or zero-quantity cart lines |
| Fixed | Pricing | Limit finite quantity tiers to the units actually remaining on each order line |
| Fixed | Pricing | Validate stored rule shapes, positive lower quantity limits, upper quantity boundaries and numeric price values; ignore invalid rows without deleting them |
| Fixed | Pricing | Reject invalid or nonpositive accounted quantities and guard relative-price calculations against malformed values |
| Fixed | Pricing | Clamp calculated unit prices to zero so excessive negative adjustments cannot create negative charges |
| Fixed | Price display | Check product and user eligibility before building a price range; preserve incoming price HTML for excluded or invalid products |
| Fixed | Admin permissions | Use WooCommerce product capabilities for the global rule post type and require manage-WooCommerce permission when saving global rule metadata |
| Fixed | Admin save protection | Add dedicated nonce checks to product and variation rule saves; use an indexed nonce for each variation editor |
| Fixed | Admin save protection | Correct autosave/revision handling and unslash submitted rule data before processing |
| Fixed | Admin validation | Whitelist settings fields, validate boolean/enum values and support empty or malformed submissions safely |
| Fixed | Admin validation | Include the cart-subtotal display checkbox in normalized checkbox settings |
| Fixed | Admin validation | Normalize product/category ID lists, remove duplicates, reject nested values and sanitize user-role lists |
| Fixed | Admin validation | Validate quantity rows and decimal/percentage inputs, rejecting malformed or reversed ranges |
| Fixed | Admin preservation | Stop overwriting the unused sale-exclusion metadata field from an unrelated form field |
| Fixed | Admin rendering | Normalize an empty rule editor to an array before counting rows; escape localized price input values |
| Fixed | Admin rendering | Handle taxonomy lookup errors and replace invalid nested paragraph markup in variation editors |
| Fixed | Admin assets | Load styles/scripts only on relevant admin screens, declare the jQuery dependency and use the plugin version for asset caching |
| Fixed | Admin row controls | Insert new rule rows only into the clicked editor instead of every editor on the page |
| Fixed | Admin row controls | Trigger a change event after adding/removing rows so WooCommerce marks variation edits as unsaved |
| Fixed | Variation display | Scope table updates to the owning product, clear missing/empty variation data and restore the parent table when a selection resets |
| Fixed | Description rendering | Restore the surrounding global post context with a finally block, including when a content callback throws |
| Fixed | Description rendering | Only render global-rule descriptions, avoiding product/variation descriptions being processed as rule descriptions |
| Fixed | Templates | Use `EXTR_SKIP` when extracting template variables, escape quantity labels and sanitize rendered description/price HTML |
| Fixed | Compatibility | Use public cart tax methods and the WooCommerce tax configuration instead of deprecated cart properties; fall back to the tax-display option |
| Fixed | Compatibility | Use public product getters/setters instead of protected generic property methods or direct properties |
| Fixed | Compatibility | Fix the legacy PRO loader to require the correct plugin file once |
| Fixed | Defensive checks | Validate rule post objects and add direct-access guards to include/template files and the legacy loader |
| Changed | Plugin identity | Add a distinct fork display name, author attribution, version, dependency headers, text-domain metadata, Update URI and identification headers |
| Added | Licensing | Add the GPL licence file and retain original authorship and licence terms |
| Added | Development examples | Initially add an optional extension adapter and isolated identification/UI fixtures; these are removed in 1.5.1 |
| Added | Tests | Add PHP syntax, pricing/admin regression and boot checks, with warnings/deprecations promoted to exceptions in the fixtures |
| Added | Tests | Add ten jQuery/jsdom DOM checks for editor rows and variation table behavior; lock development dependency versions |
| Added | Automation | Add GitHub Actions checks for PHP 7.4, 8.2, 8.3, 8.4 and 8.5 plus Node 24 DOM tests; update checkout/setup-node actions to their Node 24 versions |
| Added | Maintenance | Add review/validation notes, development instructions and ignore rules for dependencies, build output and logs |

## Verification

Before this cleanup, PHP checks passed on PHP 7.4, 8.2, 8.3, 8.4 and 8.5 (61 regression assertions and two boot scenarios per version), together with ten JavaScript DOM assertions. These checks used controlled WordPress/WooCommerce doubles, not a complete store. Future changes are verified manually on a staging store. No HPOS or Cart/Checkout Blocks compatibility flag is advertised without end-to-end validation.

## License

GPL-2.0-or-later, as declared by upstream. Soft79 retains authorship of the original plugin. Fork modifications are tracked in Git and this changelog. See LICENSE.
