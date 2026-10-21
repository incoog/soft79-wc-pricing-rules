# Validation, 2026-10-21

The maintenance branch retains upstream history through `3d495d5a79a92da069df005a6157ca92a39a68e9`.

Version 1.5.0 passed syntax checks, 61 rule-engine assertions, seven identification/UI test scenarios and both boot checks on PHP 7.4.33, 8.2.33, 8.3.33, 8.4.25 and 8.5.10 using WordPress Playground's PHP-WASM runtime. All six native GitHub Actions jobs also passed. Warnings/deprecations are promoted to exceptions in the PHP fixtures.

Version 1.5.1 removes application-specific fixtures and tests the general product exclusion filter directly. All 20 current PHP check invocations passed locally across PHP 7.4, 8.2, 8.3, 8.4 and 8.5: syntax checks for 16 PHP files, 61 rule-engine/admin assertions and the two boot scenarios per runtime. GitHub Actions repeats these checks for each published commit.

JavaScript ran under Node 24 with jQuery 3.7.1 and jsdom 30.1.3: ten DOM assertions passed. They verify scoped add/remove, variation dirty events, independent product wrappers, empty variation payloads and restoration on reset.

Covered price behavior includes threshold entry/exit, repeated totals, externally updated bases, different lines with the same product ID, product exclusion filter changes, mixed carts, cumulative eligible quantity, finite tiers, ordinary packs, zero prices, malformed rows, first/best selection, role/category restrictions and variation inheritance. Admin tests cover form shapes, decimals, whitelisted settings, capability and nonce checks, and a null rule editor. Boot checks cover missing WooCommerce and the original plugin owning the runtime before a duplicate fork.

Source review used WooCommerce's current variation-admin serialization path and documented block-hook behavior. No end-to-end WordPress/WooCommerce, live checkout, product-feed or store-theme test was performed. In particular, passing these checks does not establish full block-cart display, HPOS, third-party extension or multilingual compatibility. The plugin does not advertise those feature flags.

Before live replacement, verify the existing store's representative rules on staging: a simple product, a variable product, a cumulative category rule, quantity changes across thresholds, the site's tax display, a coupon, and its actual cart/checkout. Verify product exclusions using the general filter hook.
