CHANGELOG
---------

### Unreleased
- Fixed: `composer.json` declared `GPL-3.0-or-later`; the module is proprietary. Added `LICENSE.txt`.
- Fixed: the source repository plugin wrote every key of the raw admin POST array into `uber_inventory_source`, which allowed overwriting another source's row. It now reads the declared extension attributes and only persists an explicit allow-list.
- Fixed: the extension attributes are now persisted on `afterSave` (through the module repository) instead of `beforeSave`, so REST, CLI and data-patch saves are honoured and no orphan row is written when the core save fails.
- Fixed: a missing `organization_id` or an Uber API failure no longer aborts the core MSI source save.
- Fixed: the Uber store mapping is now created before the previous one is deleted, so a failed re-registration cannot leave a source unmapped.
- Fixed: the Uber store collection is now built per call, preventing `Not unique table/alias: 'is'` and stale filters when rates are collected more than once per request.
- Fixed: `uber_inventory_source` rows are batch-loaded once per rate estimation instead of once per candidate source.
- Fixed: `Improntus\UberInventory\Api\Data\InventorySourceInterface` now resolves to a class that implements it.
- Fixed: `organization_id` is declared and typed as a string (real values look like `W1`).
- Fixed: source addresses and Uber responses are redacted before being written to the debug log.
- Added: real `require` entries and MSI modules in `<sequence>`.
- Removed: stale `inventory_source` entry from `db_schema_whitelist.json`. Installs upgraded from a version that extended the core MSI table directly may still carry those columns on `inventory_source`; they are no longer managed by this module and must be dropped manually if unwanted.

### 1.0.5 - 2026-04-23
- Updated PHP version constraint in `composer.json` from `>=7.4.0, <8.4.0` to `>=7.4` to support current and future PHP versions (8.4+).

### 1.0.4 - 2025-05-23
- Added: A functionality is added that allows writing an Order History with the Warehouse assigned to the order.

### 1.0.3 - 2025-02-13
- Update: SourceRepository

### 1.0.2 - 2024-08-26
- Update: WarehouseRepositoryInterface / WarehouseRepository
- Added: Possibility to display a different title when quoting an order outside business hours.

### 1.0.1 - 2024-04-10
- Update Composer.json dependencies 

### 1.0.0 - 2024-04-17
- Init module
