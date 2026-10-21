# SOFT79 Pricing Rules for WooCommerce

An independently maintained update of [Soft79's original plugin](https://github.com/soft79/soft79-wc-pricing-rules), maintained by [incoog](https://github.com/incoog/soft79-wc-pricing-rules). Define quantity-based prices for products, variations, categories or combinations of products, with optional user-role restrictions and product-page price tables.

Existing pricing rules, settings, metadata keys, translations, template override paths and CSS classes are retained. No other pricing extension is required.

## Requirements and installation

WordPress 6.5+, WooCommerce 8.0+ and PHP 7.4+. These are support boundaries, not a claim that every combination has been tested end to end.

Use an installable release ZIP containing a single `soft79-wc-pricing-rules` directory. Replace the existing plugin through WordPress's ZIP upload and replacement flow. Existing rule data stays in place; no migration or price write runs on installation.

The plugin basename is retained for replacing an existing installation. The display name identifies this fork and its `Update URI` prevents an unrelated WordPress.org update from overwriting it. Automatic GitHub updates are not implemented. Do not install the development source archive with its test fixtures on a public site.

## Developer hook

The WordPress filter `soft79_wcpr_product_pricing_enabled` allows site code to exclude an individual `WC_Product` or variation from SOFT79 rules. It covers price calculations, cumulative quantities, price ranges and price tables. Without a callback, all ordinary rules remain enabled. See [hook documentation](docs/HOOKS.md) for arguments, an example and the generic fork-identification helper.

## Changelog

The comparison baseline is Soft79's final `master` commit, [`3d495d5a79a92da069df005a6157ca92a39a68e9`](https://github.com/soft79/soft79-wc-pricing-rules/commit/3d495d5a79a92da069df005a6157ca92a39a68e9), dated 2024-09-14 and labelled `1.4.5-dev.1` in the plugin header. Every fork change below is relative to that baseline. The original controller-options fix, PHP 8.2 dynamic-property fix and cart-tax display helper already existed there and are not new fork changes.

### 1.5.1, 2026-10-21

| Area | Change |
| --- | --- |
| Scope | Remove application-specific example code and its settings/detection tests. Keep this repository focused on SOFT79 itself |
| Documentation | Describe the product exclusion mechanism as a WordPress filter hook; replace the integration guide with generic hook documentation |
| Documentation | Add this complete changelog to both README.md and readme.txt, with the exact upstream comparison baseline |
| Documentation | Remove installation-specific context from the public README and update the validation notes |
| Documentation | Correct the price-display example to use current public WooCommerce functions |
| Tests | Exercise the exclusion filter directly, including no-subscriber defaults, parent/variation handling and preserving an incoming false |
| Compatibility | Preserve the filter name, fork-identification helper and existing response keys |
| Metadata | Update the plugin description and version to 1.5.1 |

### 1.5.0, 2026-10-21

| Area | Change |
| --- | --- |
| Product exclusions | Add `soft79_wcpr_product_pricing_enabled`, defaulting to true for valid products |
| Product exclusions | Apply the filter to local and global rule lookup, direct rule eligibility, price ranges, one-unit offers, product tables and variation data |
| Product exclusions | Exclude filtered products from cumulative quantity thresholds |
| Fork identification | Add a generic helper exposing the active fork identity, version and supported product exclusion capability; inactive duplicate copies do not advertise support |
| Cart recalculation | Restore the previous non-SOFT79 price before each totals pass, then clear temporary adjustment/display state |
| Cart recalculation | Remove stale discounts when quantities fall below a threshold or a product becomes excluded; avoid compounding the plugin's own discount |
| Cart recalculation | Respect an externally changed base price instead of overwriting it with an older baseline |
| Cart recalculation | Track temporary prices by product object so separate cart lines sharing a product ID retain their own base prices |
| Cart recalculation | Guard against reentrant calculations, missing carts and malformed or zero-quantity cart lines |
| Pricing | Limit finite quantity tiers to the units actually remaining on each order line |
| Pricing | Validate stored rule shapes, positive lower quantity limits, upper quantity boundaries and numeric price values; ignore invalid rows without deleting them |
| Pricing | Reject invalid or nonpositive accounted quantities and guard relative-price calculations against malformed values |
| Pricing | Clamp calculated unit prices to zero so excessive negative adjustments cannot create negative charges |
| Price display | Check product and user eligibility before building a price range; preserve incoming price HTML for excluded or invalid products |
| Admin permissions | Use WooCommerce product capabilities for the global rule post type and require manage-WooCommerce permission when saving global rule metadata |
| Admin save protection | Add dedicated nonce checks to product and variation rule saves; use an indexed nonce for each variation editor |
| Admin save protection | Correct autosave/revision handling and unslash submitted rule data before processing |
| Admin validation | Whitelist settings fields, validate boolean/enum values and support empty or malformed submissions safely |
| Admin validation | Include the cart-subtotal display checkbox in normalized checkbox settings |
| Admin validation | Normalize product/category ID lists, remove duplicates, reject nested values and sanitize user-role lists |
| Admin validation | Validate quantity rows and decimal/percentage inputs, rejecting malformed or reversed ranges |
| Admin preservation | Stop overwriting the unused sale-exclusion metadata field from an unrelated form field |
| Admin rendering | Normalize an empty rule editor to an array before counting rows; escape localized price input values |
| Admin rendering | Handle taxonomy lookup errors and replace invalid nested paragraph markup in variation editors |
| Admin assets | Load styles/scripts only on relevant admin screens, declare the jQuery dependency and use the plugin version for asset caching |
| Admin row controls | Insert new rule rows only into the clicked editor instead of every editor on the page |
| Admin row controls | Trigger a change event after adding/removing rows so WooCommerce marks variation edits as unsaved |
| Variation display | Scope table updates to the owning product, clear missing/empty variation data and restore the parent table when a selection resets |
| Description rendering | Restore the surrounding global post context with a finally block, including when a content callback throws |
| Description rendering | Only render global-rule descriptions, avoiding product/variation descriptions being processed as rule descriptions |
| Templates | Use `EXTR_SKIP` when extracting template variables, escape quantity labels and sanitize rendered description/price HTML |
| Compatibility | Use public cart tax methods and the WooCommerce tax configuration instead of deprecated cart properties; fall back to the tax-display option |
| Compatibility | Use public product getters/setters instead of protected generic property methods or direct properties |
| Compatibility | Fix the legacy PRO loader to require the correct plugin file once |
| Defensive checks | Validate rule post objects and add direct-access guards to include/template files and the legacy loader |
| Plugin identity | Add a distinct fork display name, author attribution, version, dependency headers, text-domain metadata, Update URI and identification headers |
| Licensing | Add the GPL licence file and retain original authorship and licence terms |
| Development examples | Initially add an optional extension adapter and isolated identification/UI fixtures; these are removed in 1.5.1 |
| Tests | Add PHP syntax, pricing/admin regression and boot checks, with warnings/deprecations promoted to exceptions in the fixtures |
| Tests | Add ten jQuery/jsdom DOM checks for editor rows and variation table behavior; lock development dependency versions |
| Automation | Add GitHub Actions checks for PHP 7.4, 8.2, 8.3, 8.4 and 8.5 plus Node 24 DOM tests; update checkout/setup-node actions to their Node 24 versions |
| Maintenance | Add review/validation notes, development instructions and ignore rules for dependencies, build output and logs |

## Validation and development

Run `php tests/lint.php`, `php tests/regression.php`, `php tests/boot.php` and `php tests/boot.php original-first`. For DOM tests, use Node 24+, `npm ci` and `npm test`. GitHub Actions runs the same checks on the PHP matrix above.

PHP fixtures use controlled WordPress/WooCommerce doubles. These checks do not replace testing a complete store with its actual theme, checkout and other extensions. No HPOS or Cart/Checkout Blocks compatibility flag is advertised without end-to-end validation. See [validation notes](docs/VALIDATION.md) and [the technical review](docs/REVIEW.md).

## License

GPL-2.0-or-later, as declared by upstream. Soft79 retains authorship of the original plugin. Fork modifications are tracked in Git and this changelog. See LICENSE.
