# Sprint 3 Report — Inventory Management

- **Sprint Goal:** Deliver locations, stock level tracking, append-only stock movement ledger, FEFO batch management, low-stock and near-expiry alerts, and cashier physical sales recording.
- **Dates (Provisional):** Relative Week 5 – Week 6
- **Branch:** `sprint/3-inventory`
- **Merge Target:** `develop`

## 1. Planned vs. Completed User Stories

| Story ID | Description | Owner | Status | Evidence |
|---|---|---|---|---|
| **US-07** | Stock receipts with lots and expiry for perishables | Developer A | Completed | `InventoryTest` (creates movement + batch, validates lot & expiry) |
| **US-08** | Stock transfers between warehouse and store | Developer A | Completed | `InventoryTest` (validates unreserved stock, creates transfer in/out movements, transfers batches) |
| **US-09** | Stock adjustments with mandatory reason | Developer A | Completed | `InventoryTest` (prevents negative stock, records reason in ledger) |
| **US-10** | Stock view by location & audit movement ledger | Developer B | Completed | `InventoryController` (filter by product, location, type; live available stock calculation) |
| **US-11** | Low-stock and near-expiry alerts on dashboard | Developer B | Completed | `DashboardController` & dashboard view (alerts for available &le; threshold and expiry &le; 3 days) |
| **US-24** | In-store physical sales recording (POS) | Developer B | Completed | `InventoryTest` (deducts store stock, restricted to store locations only) |

## 2. Implementation Changes

- **Database Migrations:**
  - `2026_10_25_000500_create_locations_table.php`: Physical store vs. warehouse locations; `is_online_source` flag.
  - `2026_10_25_000600_create_stock_levels_table.php`: Composite unique `(product_id, location_id)`, `quantity`, and `reserved`.
  - `2026_10_25_000700_create_batches_table.php`: Perishable lots with `lot_code`, `expiry_date`, and `quantity`.
  - `2026_10_25_000800_create_stock_movements_table.php`: Immutable audit ledger with movement types and reasons.
- **Models:**
  - `app/Models/Location.php`, `StockLevel.php`, `Batch.php`, `StockMovement.php`.
- **Domain Service:**
  - `app/Services/InventoryService.php`: Encapsulates atomic DB transactions with row-level locks (`lockForUpdate()`), preventing race conditions, managing FEFO batch consumption, and writing audit trails.
- **Controllers & Routing:**
  - `app/Http/Controllers/Staff/InventoryController.php`: Stock list per location, movement history with filters, receipt, transfer, and adjustment forms.
  - `app/Http/Controllers/Staff/StoreSaleController.php`: In-store physical sales interface.
  - `app/Http/Controllers/Staff/DashboardController.php`: Overview with dynamic low-stock and near-expiry alert cards.
- **Views:**
  - `resources/views/staff/inventory/index.blade.php`: Stock table by location.
  - `resources/views/staff/inventory/receipt.blade.php`: Stock receipt form.
  - `resources/views/staff/inventory/transfer.blade.php`: Stock transfer form.
  - `resources/views/staff/inventory/adjustment.blade.php`: Adjustment form.
  - `resources/views/staff/inventory/movements.blade.php`: Filterable movements ledger.
  - `resources/views/staff/inventory/batches.blade.php`: Batches and expiry date monitor.
  - `resources/views/staff/pos/create.blade.php`: Cashier sale screen.
  - `resources/views/staff/dashboard.blade.php`: Low-stock and near-expiry alerts widget.
- **Seeders:**
  - Updated `DatabaseSeeder` with Central Warehouse and Store locations, realistic stock levels, and expiring lots.

## 3. Test Results

Executed with `php artisan test`:
- `Tests\Feature\InventoryTest`: 8 passed (receipts, lots/expiry, transfers, transfer validation, adjustments, negative prevention, store sales, warehouse sale block).
- `Tests\Feature\CatalogTest`: 6 passed
- `Tests\Feature\ProductAdminTest`: 5 passed
- `Tests\Feature\AuthTest`: 8 passed
- `Tests\Feature\PermissionTest`: 5 passed
- `Tests\Feature\ExampleTest`: 1 passed
- `Tests\Unit\ExampleTest`: 1 passed
- **Total:** 34 passed, 104 assertions.
- Code style: Checked with `vendor/bin/pint --test` (Passed).

## 4. Known Issues & Next Sprint Carry-over

- No blockers or unresolved issues.
- Carry-over to Sprint 4: Shopping cart, online checkout (delivery vs. store pickup), order placement, stock reservation policy (`reserved += qty` with row locking), and order status lifecycle.
