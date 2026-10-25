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

- **Sprint 5 — Payments and Fulfilment (Completed & Merged into `develop`, tag `sprint-5`):**
  - Database schema & model for `payments` (supporting `mock` and `stripe` providers).
  - `PaymentService`: atomic transaction consuming stock reservations upon payment success, with audit logs and confirmation emails.
  - Multi-provider gateway support: simulated card payment UI with QA toggles and Stripe test mode integration.
  - Asynchronous webhook handling (`StripeWebhookController`) with idempotency and signature check.
  - Plain-text Spanish email notifications (`NotificationService`) on order confirmation and status changes.
  - Artisan command `orders:expire-unpaid` cancelling stale unpaid orders and releasing reserved inventory back to available stock.
  - Automated tests: `PaymentTest` (5 tests).
  - Test suite: **50 passed**, 193 assertions. Code style: `pint --test` clean.

## 2. Current Sprint

- **Next:** **Sprint 6 — Admin Management and Promotions**
- **Branch:** `sprint/6-admin-and-promotions`

## 3. Next Actions for Sprint 6

1. Database schema: `coupons` table (code, type: percentage/fixed, value, min_order_amount, max_uses, uses_count, starts_at, expires_at, is_active).
2. Implement `CouponService` with discount calculation, validity checks, and usage limits.
3. Integrate coupon input into the Checkout flow with real-time feedback and discount subtraction.
4. Staff Admin Employee Management: full CRUD for internal staff users (`admin`, `warehouse`, `cashier`) with activation/deactivation toggles and password resets.
5. Staff Admin Audit Trail Viewer: table and filters inspecting `audit_logs` (by action, entity, user, date).
6. Automated feature tests for Coupons and Admin User/Audit management.
