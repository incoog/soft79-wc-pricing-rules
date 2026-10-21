# SOFT79 Pricing Rules for WooCommerce, community fork

A maintenance fork by incoog of [Soft79's original plugin](https://github.com/soft79/soft79-wc-pricing-rules). It keeps the existing pricing-rule editor, settings keys, post type, product metadata, translations, template override paths and CSS classes.

The customer's supplied live archive was compared byte-for-byte after line-ending normalization with upstream tag `1.4.3`. All 26 files matched. This work builds on upstream commit `3d495d5a79a92da069df005a6157ca92a39a68e9`, retaining the later controller-options and PHP 8.2 fixes.

The maintained fork is [incoog/soft79-wc-pricing-rules](https://github.com/incoog/soft79-wc-pricing-rules). The plugin headers and optional adapter use this repository as the fork identity and source location.

## Optional integration

SOFT79 runs independently. No campaign plugin is loaded or required. With no filter callback attached, all original rules remain enabled.

The active fork provides `soft79_wcpr_get_integration_info()` with a stable fork ID, integer API version and supported features. A separate campaign plugin can return `false` from `soft79_wcpr_product_pricing_enabled` for an individual `WC_Product`. See [the integration contract](docs/INTEGRATION.md).

`examples/campaign-plugin/class-soft79-integration.php` belongs to the future campaign plugin. SOFT79 never loads it. It implements the requested capability detection, optional switch rendering and product gate using callbacks supplied by that plugin. It is an adapter, not an implemented campaign engine or standalone settings page.

## Installation

Use the installable release ZIP, which contains a single `soft79-wc-pricing-rules` directory. Replace the existing plugin through WordPress's ZIP upload and replacement flow. Do not uninstall/delete its data first. The plugin basename is intentionally retained to support replacing the live installation; the display name identifies the community fork and `Update URI` protects it from unrelated WordPress.org updates. This header does not implement automatic GitHub updates.

No database migration, rule deletion or price write happens on installation. Existing rules and settings remain in their original storage. Do not run two renamed copies simultaneously; if the original owns the runtime first, the duplicate fork does not advertise its API.

Requirements: WordPress 6.5+, WooCommerce 8.0+, PHP 7.4+. Minimum requirements are a support boundary, not a claim that every combination has been integration-tested. Current validation uses PHP runtimes and WP/WC doubles, plus DOM tests. A complete WordPress/WooCommerce installation and the customer's theme, checkout, tax setup and additional extensions have not been exercised. See [validation](docs/VALIDATION.md).

## Development

Run `php tests/lint.php`, `php tests/regression.php`, `php tests/boot.php` and `php tests/boot.php original-first`. Run `php tests/detection.php STATE` for each state in the CI workflow. The PHP test suite requires no database or dependencies.

For DOM tests, use Node 24+, `npm ci`, then `npm test`. GitHub Actions repeats PHP checks on 7.4, 8.2, 8.3, 8.4 and 8.5. A source archive is separate from the installable plugin ZIP; test fixtures must not be deployed into a public plugin directory.

## License

GPL-2.0-or-later, as declared by upstream. Soft79 retains authorship of the original plugin. Modified files and changes are tracked in Git and documented in this repository. See LICENSE.
