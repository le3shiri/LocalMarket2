# Sprint 6 Report — LocalMarket

**Sprint Goal:** Implement the promotion and discount coupon engine, staff user administration (RBAC assignment, activation/deactivation controls), and the complete audit trail log viewer for business accountability.

**Dates:** Sprint 6  
**Status:** Completed  
**Branch:** `sprint/6-admin-and-promotions`  
**Git Tag:** `sprint-6`  

---

## 1. Summary of Deliverables

### Database & Migrations
- `database/migrations/2026_10_25_001100_create_coupons_table.php`:
  - `coupons`: Code (unique, uppercase), description, discount type (`percentage` or `fixed`), value, minimum order threshold (`min_order_amount`), usage constraints (`max_uses`, `uses_count`), temporal validity (`starts_at`, `expires_at`), and `is_active` flag.

### Domain Models & Services
- `app/Models/Coupon.php`: Eloquent model with validation methods (`isValidForAmount`), discount calculation (`calculateDiscount`), and display helpers (`typeLabel`, `valueFormatted`).
- `app/Services/CouponService.php`:
  - Coupon application into customer session with validation against limits, minimum order amounts, and expiration dates.
  - Usage tracking (`recordUsage`) updating counters and logging audit records.
- `app/Services/OrderService.php`:
  - Updated `createOrder()` to incorporate active session coupon discounts, persist `coupon_id` and `coupon_code` into the order record, and decrement/track coupon usage atomically within the database transaction.

### Controllers, Views & Navigation
- `app/Http/Controllers/CheckoutController.php` & `resources/views/checkout/index.blade.php`:
  - Added coupon code input form in checkout summary with live discount application and remove buttons.
- `app/Http/Controllers/Staff/CouponController.php` & `resources/views/staff/coupons/`:
  - Full CRUD interface for administrators to create, edit, activate, or delete promotional coupons.
- `app/Http/Controllers/Staff/UserController.php` & `resources/views/staff/users/`:
  - Staff management panel allowing administrators to create staff accounts (`admin`, `warehouse`, `cashier`), edit credentials, assign roles, and toggle activation status (preventing self-deactivation).
- `app/Http/Controllers/Staff/AuditController.php` & `resources/views/staff/audit/index.blade.php`:
  - Comprehensive audit log inspection interface with filters by action, actor, and date ranges, showing JSON metadata payloads and IP addresses.

---

## 2. Testing & Quality Assurance

- **Coupon Engine Tests (`tests/Feature/CouponTest.php`):**
  - Admin coupon creation and persistence.
  - Customer discount application during checkout (percentage calculation).
  - Minimum purchase order validation (rejection when subtotal is below minimum threshold).
  - Rejection of expired or inactive coupons.
- **Admin Management & Audit Tests (`tests/Feature/AdminManagementTest.php`):**
  - Admin inspection of user list and role filtering.
  - Creation of staff accounts with role assignment and audit trail recording.
  - Activation toggle enforcement (deactivated accounts are immediately blocked from logging in).
  - Self-deactivation prevention for administrators.
  - Audit trail viewer rendering and searching.

### Test Execution Summary
- **Total Test Cases:** 59 tests across 11 test suites.
- **Total Assertions:** 227 assertions.
- **Failures:** 0.
- **Code Style (Laravel Pint):** 100% compliant.

---

## 3. Sprint Sign-Off
- **Developer A & Developer B Sign-off:** Approved.
- **Next Sprint:** Sprint 7 — Stabilization, Comprehensive Seeders, E2E Review, Documentation & Final Delivery.
