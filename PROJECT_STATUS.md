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

- **Sprint 3 — Inventory (Completed & Merged into `develop`, tag `sprint-3`):**
  - Locations management (`locations`: warehouse vs. store, with `is_online_source`).
  - Stock levels (`stock_levels`: composite unique `product_id` + `location_id`, physical `quantity` and `reserved`).
  - Batch/Lot tracking (`batches`) with lot codes and expiry dates for perishable products.
  - Immutable stock movements ledger (`stock_movements`) attributing every change with type, signed quantity, user, and reason.
  - `InventoryService` handling atomic transactions with `lockForUpdate()`, FEFO batch deduction, and race condition prevention.
  - Warehouse stock management UI (receipts, transfers, adjustments, movements audit trail).
  - Cashier in-store sales screen (POS) discounting store inventory in real time.
  - Staff Dashboard with real-time low-stock and near-expiry (&le; 3 days) alert cards.
  - Automated tests: `InventoryTest` (8 tests).

- **Sprint 4 — Cart and Orders (Completed & Merged into `develop`, tag `sprint-4`):**
  - Schema & models for `orders`, `order_items`, and `order_status_histories`.
  - Shopping Cart (`CartService`) with unit-level validation and online available stock enforcement.
  - Interactive Cart UI (`cart/index.blade.php`) and live Add-to-Cart form on product view.
  - Multi-step Checkout (`checkout/index.blade.php`) with Barcelona city postcode validation (08001–08042) and free delivery rules.
  - Atomic stock reservation (`reserved += qty`) with pessimistic database row locks preventing overselling.
  - Customer Order tracking & timeline (`account/orders/index.blade.php`, `show.blade.php`).
  - Back-office Staff Order processing (`staff/orders/index.blade.php`, `show.blade.php`) with status transition rules.
  - Automated tests: `CheckoutTest` (5 tests), `OversellingTest` (2 tests), `OrderStatusTest` (4 tests).
  - Test suite: **45 passed**, 155 assertions. Code style: `pint --test` clean.

## 2. Current Sprint

- **Next:** **Sprint 5 — Payments and Fulfilment**
- **Branch:** `sprint/5-payments-and-notifications`

## 3. Next Actions for Sprint 5

1. Database schema: `payments` table (order reference, payment provider `stripe`/`mock`, method, amount, currency, status, transaction ID, raw payload).
2. Implement `PaymentService` supporting simulated mock card payments and Stripe test mode.
3. Customer payment UI and payment simulation screens.
4. Stripe webhook handling controller (`StripeWebhookController`) with signature verification and idempotency.
5. `NotificationService` sending order confirmation and status update emails (log/smtp mail driver in Spanish).
6. Console command for unpaid order expiry (`php artisan orders:expire-unpaid`) releasing reserved stock after configured timeout (e.g. 30 minutes).
7. Feature tests for payments, webhook idempotency, and automated stock reservation release on expiry.
