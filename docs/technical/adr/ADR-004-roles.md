# ADR-004 — Roles: single role column + middleware

- **Status:** Accepted

## Context
Four roles are confirmed (admin, warehouse technician, store worker/cashier, customer). There is no
requirement for custom per-user permissions.

## Decision
- `users.role` ∈ `admin | warehouse | cashier | customer`.
- `App\Http\Middleware\EnsureRole` registered as alias `role`, used in `routes/web.php`:
  `->middleware('role:admin,warehouse')`.
- Finer rules (e.g. only admin cancels a paid order, cashier sells only at store locations) are checked
  inside controllers/services.
- Views hide links the user cannot use, but **the server always re-checks**.

## Consequences
Very little code. If the client later needs configurable permissions, migrate to a `roles`/`permissions`
table or `spatie/laravel-permission`.
