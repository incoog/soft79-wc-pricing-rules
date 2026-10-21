# Validation, 2026-10-21

The supplied live ZIP matches upstream tag 1.4.3 after line-ending normalization. The prepared maintenance branch retains upstream history through `3d495d5a79a92da069df005a6157ca92a39a68e9`.

| Runtime | Syntax | Rule-engine assertions | Detection assertions | Boot checks |
| --- | --- | --- | --- | --- |
| PHP 7.4.33 | Pass, 18 PHP files | 61 pass | 38 pass in seven isolated scenarios | Both pass |
| PHP 8.2.33 | Pass, 18 PHP files | 61 pass | 38 pass in seven isolated scenarios | Both pass |
| PHP 8.3.33 | Pass, 18 PHP files | 61 pass | 38 pass in seven isolated scenarios | Both pass |
| PHP 8.4.25 | Pass, 18 PHP files | 61 pass | 38 pass in seven isolated scenarios | Both pass |
| PHP 8.5.10 | Pass, 18 PHP files | 61 pass | 38 pass in seven isolated scenarios | Both pass |

PHP ran in WordPress Playground's PHP-WASM runtime, with all PHP warnings/deprecations promoted to exceptions in the regression fixtures. The WP/WC dependencies in those fixtures are controlled test doubles, not a running WordPress installation. The 55 PHP check invocations passed. Git whitespace validation passed.

JavaScript ran under Node 24 with jQuery 3.7.1 and jsdom 30.1.3: ten DOM assertions passed. They verify scoped add/remove, variation dirty events, independent product wrappers, empty variation payloads and restoration on reset.

Covered price behavior includes threshold entry/exit, repeated totals, externally updated bases, different lines with the same product ID, optional integration toggling, mixed carts, cumulative eligible quantity, finite tiers, ordinary packs, zero prices, malformed rows, first/best selection, role/category restrictions and variation inheritance. Admin tests cover form shapes, decimals, whitelisted settings, capability and nonce checks, and a null rule editor. Boot checks cover missing WooCommerce and the original plugin owning the runtime before a duplicate fork.

Source review used WooCommerce's current variation-admin serialization path and documented block-hook behavior. No end-to-end WordPress/WooCommerce, live checkout, product-feed or customer-theme test was performed. In particular, passing these checks does not establish full block-cart display, HPOS, third-party extension or multilingual compatibility. The plugin does not advertise those feature flags.

Before live replacement, verify the existing store's representative rules on staging: a simple product, a variable product, a cumulative category rule, quantity changes across thresholds, the site's tax display, a coupon, and its actual cart/checkout. Verify campaign exclusions after the separate campaign engine is implemented.
