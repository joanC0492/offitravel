# Offitravel Cabin Supplement Base

## Purpose

This MU plugin provides the reusable administrative and calculation foundation for product-scoped cabin supplements. Checkpoint 4 deliberately stops before public activation: it does not render cabin fields on a tour, attach JavaScript to a booking form, recalculate AJAX totals, alter a cart price, or persist data in a WooCommerce order.

The implementation is isolated from the existing room-mode and product-add-on systems. No provider file or Checkpoint 1–3 file needs to change.

## Files

- `offitravel-cabin-supplements.php`: administrative configuration, authoritative PHP calculator, and snapshot normalization.
- `offitravel-cabin-supplements-admin.js`: row management for the product metabox.
- `offitravel-cabin-supplements-state.js`: pure state helpers reserved for a future public interface. It is not enqueued in Checkpoint 4.

## Administrative configuration

OVA rental products receive an **Opciones de cabina — Base técnica** metabox with these fields per option:

- Internal identifier.
- Public label.
- Supplement per person.

The option list is stored under:

```text
_offitravel_cabin_options
```

Each normalized row has this shape:

```php
array(
    'id'               => 'normalized-id',
    'label'            => 'Public label',
    'price_per_person' => '14.37',
)
```

The following activation key is reserved for a later checkpoint:

```text
_offitravel_cabin_options_enabled
```

Checkpoint 4 never writes that key and does not expose an activation control. Consequently, storing option rows alone cannot activate a public selector.

### Safe save behavior

The metabox includes an interaction marker whose initial value is zero. The administrative script changes it to one only after the user edits a field, adds a row, or removes a row. The PHP save handler exits before any metadata operation when the marker is absent or remains zero.

This means that opening or saving a product without interacting with the metabox performs no creation, migration, update, or deletion of cabin metadata.

After an explicit interaction, PHP validates the complete submitted option list before writing:

- Rows that are entirely empty are ignored.
- Partially completed rows are rejected.
- IDs are normalized with `sanitize_key()` and must be unique.
- Labels must be present and must remain non-empty after WordPress text sanitization.
- Prices must be non-negative decimal values and are normalized using WooCommerce precision.

An invalid list causes no metadata write. A valid empty list removes only `_offitravel_cabin_options`; it does not alter activation or booking metadata.

## Authoritative PHP calculator

`offitravel_cabin_calculate_snapshot()` is a callable primitive for later checkpoints. It is not registered on a public hook in Checkpoint 4.

Its inputs are:

```php
$product_id;
$raw_cabins = array(
    1 => array(
        'people'   => '2',
        'category' => 'normalized-id',
    ),
);
$context = array(
    'offitravel_room_count'  => 1,
    'offitravel_room_people' => array( 2 ),
);
```

The calculator:

1. Resolves the ID with `wc_get_product()` and requires an existing WooCommerce product of type `ovabrw_car_rental`.
2. Requires explicit product activation through the reserved metadata key.
3. Loads option labels and prices from WordPress metadata.
4. Validates the current room count and occupants from the trusted context.
5. Requires one selection per cabin and exact occupant agreement.
6. Ignores any submitted price, subtotal, or total.
7. Calculates each subtotal as occupants multiplied by the stored per-person price.
8. Applies `wc_format_decimal()` and `wc_get_price_decimals()` through the money snapshot helper.

No maximum number of cabins or occupants is imposed here. Those limits must come from the product’s valid room configuration in a future integration layer.

## Snapshot model

The canonical calculation result is structured as follows:

```text
version
product_id
cabins[CABIN_INDEX]
  cabin_index
  occupants
  category
  label
  price_per_person
  subtotal
total
```

`offitravel_cabin_normalize_snapshot()` validates and rebuilds historical subtotals from the snapshot itself without consulting current product pricing. This prepares a stable persistence boundary for later checkout and order work while retaining the values charged at booking time.

Snapshot normalization rejects product identifiers that become zero after `absint()`, preventing malformed historical payloads from being treated as product-owned data.

`offitravel_cabin_snapshot_total()` returns the validated aggregate as a float or zero for an invalid snapshot. Checkpoint 4 does not call it from any WooCommerce pricing hook.

## JavaScript state contract

`offitravel-cabin-supplements-state.js` is a pure, unqueued module that prepares these later behaviors:

- Normalize a positive configured occupancy without inventing a maximum.
- Preserve categories for cabin positions that survive a room rebuild.
- Add empty selections for newly created cabin positions.
- Remove state for cabin positions that no longer exist.
- Build a minimal payload containing only `people` and `category`.

The future request shape is:

```text
offitravel_cabins[ROOM_INDEX][people]
offitravel_cabins[ROOM_INDEX][category]
```

Client-side prices and subtotals are intentionally absent.

## Registered hooks

Only administrative hooks are registered:

- `add_meta_boxes_product`
- `woocommerce_process_product_meta`
- `admin_enqueue_scripts`
- `redirect_post_location`
- `admin_notices`

There are no public render, AJAX, price, cart, session, checkout, order, email, or frontend enqueue hooks in this checkpoint.

## Dependencies and compatibility

- WordPress metadata and nonce APIs.
- WooCommerce decimal precision helpers and product types.
- The existing OVA rental product type is detected read-only to decide where the metabox belongs.

The plugin does not modify the existing fixed add-ons, traveler-age insurance, room-mode implementation, cart snapshots, session restoration, checkout display, order metadata, or emails.

Calculator tests use a real OVA product only for type resolution. Activation, option labels, and prices are supplied through temporary read filters with synthetic values; the tests create no product and perform no metadata write.

## Deferred work

Later checkpoints must provide and test, per explicitly configured product:

- Activation and commercial option data.
- Integration with the product’s current room/cabin occupancy rules.
- Public rendering and event handling.
- Extension of the existing AJAX payload mechanism without a second global interceptor.
- Cart validation and idempotent line pricing.
- Session, checkout, order, administration, and email persistence/display.
- Product-specific handling of a one-person cabin and any individual supplement.

None of those behaviors is active in Checkpoint 4.
