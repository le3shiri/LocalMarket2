# Project Status — LocalMarket

Last Updated: 2026-10-25

## 1. Summary of Completed Milestones

- **Sprint 0 — Discovery and Setup (Completed & Merged into `develop`, tag `sprint-0`):**
  - Project requirements analysis, glossary, business rules, assumptions log.
  - Complete Agile setup: product backlog, user stories, DoD, DoR, sprint planning, risk register, decision log.
  - Architecture Decision Records (ADRs 001–004) and editable Mermaid diagrams (01–13).
  - Baseline Laravel 12 setup with MySQL configuration and GitHub Actions CI.

- **Sprint 1 — Foundation (Completed & Verified on `sprint/1-foundation`):**
  - Database schema: User role fields (`role`, `phone`, `is_active`) and append-only `audit_logs` table.
  - Server-side role-based authorization via `EnsureRole` middleware.
  - Complete authentication flows: login (with deactivation block and role-based redirect), registration (strictly customer), password reset with Spanish mail notification.
  - Global formatting helpers for EUR currency and quantities (`eur()`, `qty()`, `fdate()`).
  - Cohesive Spanish design system (`public/css/app.css`, `public/js/app.js`), customer layout (`app.blade.php`), and staff management layout (`staff.blade.php`).
  - Database seeder with demo accounts for each role (`admin`, `almacen`, `cajero`, `cliente`).
  - Automated tests: `AuthTest` (8 tests) and `PermissionTest` (5 tests). All 15 tests pass. Code style passes `pint --test`.

## 2. Current Sprint

- **Branch:** `sprint/1-foundation` ready for merge into `develop` and tagging `sprint-1`.

## 3. Next Actions

1. Merge `sprint/1-foundation` into `develop` and tag `sprint-1`.
2. Start **Sprint 2 — Catalogue**:
   - Create migrations and models for `categories` and `products`.
   - Implement customer product catalog with filtering and search (`CatalogController`, `catalog/index.blade.php`, `catalog/show.blade.php`).
   - Implement admin product and category management.
   - Add feature tests for catalog browsing and admin management.
