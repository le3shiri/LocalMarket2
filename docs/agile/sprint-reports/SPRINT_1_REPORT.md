# Sprint 1 Report — Foundation

- **Sprint Goal:** Establish base layout, authentication, role-based authorization (RBAC), user model, audit logging foundation, and baseline testing.
- **Dates (Provisional):** Relative Week 1 – Week 2
- **Branch:** `sprint/1-foundation`
- **Merge Target:** `develop`

## 1. Planned vs. Completed User Stories

| Story ID | Description | Owner | Status | Evidence |
|---|---|---|---|---|
| **US-01** | User registration and authentication | Developer B | Completed | `AuthTest` (registration assigns `customer`, login works, deactivation blocked) |
| **US-02** | Password reset via email token | Developer A | Completed | `AuthTest` (password reset token request handling) |
| **US-03** | Role-based authorization & navigation | Developer A | Completed | `PermissionTest` (403 forbidden checks, guest redirects, staff route access) |
| **Base Layout** | Spanish design system, responsive shop & staff views | Developer B | Completed | `resources/views/layouts/app.blade.php`, `staff.blade.php`, `public/css/app.css` |

## 2. Implementation Changes

- **Database Migrations:**
  - `2026_10_25_000100_add_role_fields_to_users_table.php`: Added `role` (admin, warehouse, cashier, customer), `phone`, and `is_active`.
  - `2026_10_25_000200_create_audit_logs_table.php`: Created `audit_logs` table for append-only audit tracking.
- **Models & Middlewares:**
  - `app/Models/User.php`: Added role constants, role helpers (`hasRole()`, `isStaff()`, `roleLabel()`), and casts.
  - `app/Models/AuditLog.php`: Model with `record()` helper method.
  - `app/Http/Middleware/EnsureRole.php`: Server-side authorization middleware protecting staff routes.
- **Controllers & Routing:**
  - `app/Http/Controllers/Auth/LoginController.php`
  - `app/Http/Controllers/Auth/RegisterController.php`
  - `app/Http/Controllers/Auth/PasswordResetController.php`
  - `routes/web.php`: Registered public routes, auth routes, and role-protected `/gestion` routes.
- **Frontend & Views:**
  - Design system in `public/css/app.css` and `public/js/app.js`.
  - Layouts in `resources/views/layouts/app.blade.php` and `resources/views/layouts/staff.blade.php`.
  - Authentication views in `resources/views/auth/`.
  - Pagination in `resources/views/partials/pagination.blade.php`.
  - Staff landing in `resources/views/staff/dashboard.blade.php`.
- **Seeders:**
  - `database/seeders/DatabaseSeeder.php` seeded with accounts for `admin`, `warehouse`, `cashier`, `cliente`, and a `desactivado` user.

## 3. Test Results

Executed with `php artisan test`:
- `Tests\Unit\ExampleTest`: 1 passed
- `Tests\Feature\AuthTest`: 8 passed
- `Tests\Feature\PermissionTest`: 5 passed
- `Tests\Feature\ExampleTest`: 1 passed
- **Total:** 15 passed, 43 assertions.
- Code style: Checked with `vendor/bin/pint --test` (Passed).

## 4. Known Issues & Next Sprint Carry-over

- No blockers or unresolved defects in Sprint 1.
- Carry-over to Sprint 2: Product catalogue implementation (`categories`, `products`, customer browsing, and admin catalogue management).
