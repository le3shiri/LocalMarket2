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

- **Sprint 2 — Catalogue (Completed & Verified on `sprint/2-product-catalog`):**
  - Database schema & models for `categories` and `products` (supporting sales units: `unit`, `kg`, `pack`).
  - Customer-facing homepage (`welcome.blade.php`) and searchable/filterable catalogue (`catalog/index.blade.php`, `catalog/show.blade.php`).
  - Administrator catalogue management: full CRUD for categories and products with server-side validation and audit logging (`AuditLog::record`).
  - Automated tests: `CatalogTest` (6 tests) and `ProductAdminTest` (5 tests).
  - Test suite: 26 passed, 78 assertions. Code style: `pint --test` passed.

## 2. Current Sprint

- **Branch:** `sprint/2-product-catalog` ready for merge into `develop` and tagging `sprint-2`.

## 3. Next Actions

1. Merge `sprint/2-product-catalog` into `develop` and tag `sprint-2`.
2. Start **Sprint 3 — Inventory**:
   - Create migrations and models for `locations`, `stock_levels`, `stock_movements`, and `batches`.
   - Implement `InventoryService` for atomic transactions with row-level locks (`SELECT ... FOR UPDATE`).
   - Implement warehouse receipt, transfer, and adjustment workflows.
   - Implement low-stock and near-expiry alerts on dashboard.
   - Implement physical store sale (POS) recording for cashier role.
   - Add feature tests for inventory consistency, transfer validations, and batch tracking.
