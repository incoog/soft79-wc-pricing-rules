# Review and changes

All executable PHP, JavaScript, templates, CSS and configuration in the supplied 26-file plugin were read as a connected system. Translation catalogs were retained. Upstream history was inspected and the live archive matched tag 1.4.3 after newline normalization.

| Finding | Resolution |
| --- | --- |
| Live PRO controller discards supplied settings | Retain upstream constructor fix |
| PHP 8.2 dynamic controller property | Retain upstream declared property |
| Deprecated cart tax-display properties | Public cart methods and WC tax configuration |
| Quantity reduction leaves previous temporary discount in place | Restore own baseline and clear temporary display state before each totals pass |
| Repeated totals or changing exclusions can retain/compound a price | Repeatable recalculation and a reentrancy guard |
| Multiple cart lines sharing an ID overwrite their price baselines | Temporary state belongs to the individual product object |
| Finite tier can apply more units than exist on a line | Cap tier quantity at remaining line quantity |
| No reliable per-product integration contract | Versioned active-fork API and product gate throughout calculation/display |
| Excluded products could otherwise unlock cumulative discounts | Gate rule eligibility during cart discovery |
| Malformed rows can trigger warnings or division by zero | Validate stored rows, posted shapes, quantity boundaries and relative prices |
| Extreme negative discounts can produce negative prices | Clamp computed unit prices to zero |
| Product/variation rule saves lack their own nonce checks | Dedicated nonces and edit permissions; variation nonce indexed per editor row |
| Global custom post type uses ordinary post capabilities | Use WooCommerce product capabilities and manage-WooCommerce check on rule metadata saves |
| Options accept arbitrary shapes and unrecognized fields | Whitelisted booleans/enums, safe empty form handling |
| Rule IDs, categories and roles accept malformed/nested values | Normalize and sanitize posted values |
| Editor may call count on null | Normalize to an empty array |
| Add-row modifies all visible rule editors | Scope insertion to clicked editor |
| Add/remove does not mark a variation dirty | Emit change after row operations |
| Variation selection updates every discount wrapper | Scope to the owning product; clear stale data and restore parent table on reset |
| Description rendering replaces surrounding global post | Save/restore context with finally, including error paths |
| Legacy PRO entry point points at missing file | Require the real entry point once |
| Unscoped admin assets and missing jQuery dependency | Enqueue on relevant screens with jQuery and the plugin version |
| Invalid nested paragraph layout in variation editor | Use div wrapper |
| Template variable extraction can overwrite local variables | EXTR_SKIP |
| Catalog range can include rules invalid for user/product | Validate before building a range |
| Fork could be overwritten as an original plugin update | Distinct display name and Update URI |

Stored rule keys, settings, parent inheritance, first/best selection, fixed amounts, percentages, standard pack rules, translations, template paths and existing CSS hooks are retained. No SOFT79 rule or price data is deleted or migrated on activation. Invalid legacy rows are ignored at runtime, not silently deleted from storage.

Remaining integration boundaries: third-party plugins that mutate cart prices after SOFT79 or save cart product objects, theme template overrides, multilingual plugin behavior, grouped/bundled/subscription products, cumulative pack allocation across multiple different order lines, and exact block-cart display behavior require a real staging installation. The tests cover the supplied rule engine and optional integration; they are not a complete security certification or a promise of compatibility with every extension. No HPOS or Cart/Checkout Blocks compatibility flag is advertised without end-to-end validation.
