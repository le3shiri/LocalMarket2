# Project Status — LocalMarket

Last Updated: 2026-10-25

## 1. Summary of Completed Milestones

- **Sprint 0 — Discovery and Setup (Completed & Merged into `develop`, tag `sprint-0`):**
  - Project requirements analysis, glossary, business rules, assumptions log.
  - Complete Agile setup: product backlog, user stories, DoD, DoR, sprint planning, risk register, decision log.
  - Architecture Decision Records (ADRs 001–004) and editable Mermaid diagrams (01–13).
  - Baseline Laravel 12 setup with MySQL configuration and GitHub Actions CI.

- **Sprint 1 — Foundation (Completed & Merged into `develop`, tag `sprint-1`):**
  - Database schema: User role fields (`role`, `phone`, `is_active`) and append-only `audit_logs` table.
  - Server-side role-based authorization via `EnsureRole` middleware.
  - Complete authentication flows: login (with deactivation block and role-based redirect), registration (strictly customer), password reset with Spanish mail notification.
  - Global formatting helpers for EUR currency and quantities (`eur()`, `qty()`, `fdate()`).
  - Cohesive Spanish design system (`public/css/app.css`, `public/js/app.js`), customer layout (`app.blade.php`), and staff management layout (`staff.blade.php`).
  - Automated tests: `AuthTest` (8 tests) and `PermissionTest` (5 tests).

- **Sprint 2 — Catalogue (Completed & Merged into `develop`, tag `sprint-2`):**
  - Database schema & models for `categories` and `products` (supporting sales units: `unit`, `kg`, `pack`).
  - Customer-facing homepage (`welcome.blade.php`) and searchable/filterable catalogue (`catalog/index.blade.php`, `catalog/show.blade.php`).
  - Administrator catalogue management: full CRUD for categories and products with server-side validation and audit logging (`AuditLog::record`).
  - Automated tests: `CatalogTest` (6 tests) and `ProductAdminTest` (5 tests).

- **Sprint 3 — Inventory (Completed & Verified on `sprint/3-inventory`):**
  - Locations management (`locations`: warehouse vs. store, with `is_online_source`).
  - Stock levels (`stock_levels`: composite unique `product_id` + `location_id`, physical `quantity` and `reserved`).
  - Batch/Lot tracking (`batches`) with lot codes and expiry dates for perishable products.
  - Immutable stock movements ledger (`stock_movements`) attributing every change with type, signed quantity, user, and reason.
  - `InventoryService` handling atomic transactions with `lockForUpdate()`, FEFO batch deduction, and race condition prevention.
  - Warehouse stock management UI (receipts, transfers, adjustments, movements audit trail).
  - Cashier in-store sales screen (POS) discounting store inventory in real time.
  - Staff Dashboard with real-time low-stock and near-expiry (&le; 3 days) alert cards.
  - Automated tests: `InventoryTest` (8 tests).
  - Test suite: **34 passed**, 104 assertions. Code style: `pint --test` passed.

## 2. Current Sprint

- **Branch:** `sprint/3-inventory` ready for merge into `develop` and tagging `sprint-3`.

## 3. Next Actions

1. Merge `sprint/3-inventory` into `develop` and tag `sprint-3`.
2. Start **Sprint 4 — Cart and Orders**:
   - Create migrations and models for `orders`, `order_items`, and `order_status_histories`.
   - Implement `CartService` (session-based cart, unit validation, available online stock limits).
   - Implement Cart UI (`cart/index.blade.php`).
   - Implement `OrderService` and Checkout flow (`checkout/index.blade.php`):
     - Delivery (Barcelona postcodes check, configurable fee) vs. Store pickup (free).
     - Atomic stock reservation (`reserved += qty`) with `lockForUpdate()`.
     - Creation of order in `pending_payment` state.
   - Implement Customer Order History (`account/orders/index.blade.php`, `account/orders/show.blade.php`).
   - Implement Staff Order Management with allowed state transitions per role.
   - Add feature tests for Cart, Checkout, Stock Reservation, and Overselling prevention (`CheckoutTest`, `OversellingTest`, `OrderStatusTest`).
