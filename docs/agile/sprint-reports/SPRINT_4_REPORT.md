# Sprint 4 Report — LocalMarket

**Sprint Goal:** Implement the online customer shopping cart, checkout workflow with Barcelona postcode validation (08001–08042), atomic stock reservation with concurrency protection against overselling, customer order tracking, and staff order management with state machine transitions.

**Dates:** Sprint 4  
**Status:** Completed  
**Branch:** `sprint/4-orders-and-cart`  
**Git Tag:** `sprint-4`  

---

## 1. Summary of Deliverables

### Database & Migrations
- `database/migrations/2026_10_25_000900_create_orders_table.php`:
  - `orders`: Unique human-readable code (`LM-YYYYMMDD-XXXXX`), customer snapshots, payment/fulfilment status, delivery address snapshots, and amounts.
  - `order_items`: Snapshot of price, product name, quantity, and line total at time of purchase.
  - `order_status_histories`: Audit trail of all state transitions with timestamp, actor (`user_id`), previous status, next status, and operational notes.

### Domain Models & Logic
- `app/Models/Order.php`: Defined status constants (`pending_payment`, `confirmed`, `preparing`, `ready_for_pickup`, `out_for_delivery`, `completed`, `cancelled`), allowed state transitions matrix, badges, and helper methods.
- `app/Models/OrderItem.php`: Relationship to Order and Product, formatted quantity calculation.
- `app/Models/OrderStatusHistory.php`: Relationship to Order and User.
- `app/Services/CartService.php`: Session-based shopping cart with unit sanitization (kg steps of 0.25; unit/pack whole integers) and online available stock validation.
- `app/Services/OrderService.php`: Atomic transaction creating orders, locking rows (`lockForUpdate()`), reserving stock, clearing customer cart, and transitioning statuses with stock return/release.
- `app/Services/InventoryService.php`: Added `reserveStock()`, `releaseReservation()`, `consumeReservation()`, and `returnStock()`.

### Controllers & Routes
- `app/Http/Controllers/CartController.php`: Add, update, remove, and clear cart actions with flash feedback.
- `app/Http/Controllers/CheckoutController.php`: Checkout view and checkout submission with Barcelona postcode (08001–08042) enforcement and free delivery logic (>= 50 EUR).
- `app/Http/Controllers/AccountOrderController.php`: Customer order list, order detail view, and customer self-cancellation for unpaid orders.
- `app/Http/Controllers/Staff/OrderController.php`: Back-office order management table with status/search filtering, order inspection, and workflow transitions (restricted by role).
- `routes/web.php`: Registered cart, checkout, customer account, and staff order management routes.

### Views (UI/UX)
- `resources/views/cart/index.blade.php`: Responsive shopping cart table, quantity adjustments, empty state, and checkout action.
- `resources/views/checkout/index.blade.php`: Multi-step checkout form with delivery/pickup selection, contact info, address, delivery cost calculator, and item summary.
- `resources/views/account/orders/index.blade.php` & `show.blade.php`: Customer portal order history and order tracking timeline.
- `resources/views/staff/orders/index.blade.php` & `show.blade.php`: Back-office staff order processing interface with status transition selector and full audit log.
- `resources/views/catalog/show.blade.php`: Updated with live Add to Cart form.

---

## 2. Testing & Quality Assurance

- **Checkout Flow (`tests/Feature/CheckoutTest.php`):**
  - Add to cart and view cart.
  - Guest redirect to login upon checkout attempt.
  - Postcode restriction validation rejecting non-Barcelona postcodes.
  - Order creation and inventory stock reservation.
  - Free delivery threshold application (50 EUR).
- **Concurrency & Overselling Prevention (`tests/Feature/OversellingTest.php`):**
  - Rejection when customer tries to add more than available stock to cart.
  - Atomic database rollback and user error notification if available stock is exhausted during checkout before reservation succeeds.
- **Order State Machine & Access Control (`tests/Feature/OrderStatusTest.php`):**
  - Customer data isolation (customers cannot inspect other customers' orders).
  - Unpaid order cancellation by customer releases reserved stock back to available pool.
  - Staff state progression (`pending_payment` -> `confirmed` -> `preparing` -> `ready_for_pickup` -> `completed`).
  - Validation blocking delivery-only transitions on pickup orders.
  - Admin cancellation of paid orders restores physical stock to inventory.

### Test Execution Summary
- **Total Test Cases:** 45 tests across 8 suites.
- **Total Assertions:** 155 assertions.
- **Failures:** 0.
- **Code Style (Laravel Pint):** 100% compliant.

---

## 3. Sprint Sign-Off
- **Developer A & Developer B Sign-off:** Approved.
- **Next Sprint:** Sprint 5 — Payment Integration (Stripe Test / Simulated POS) & Customer Notification Flow.
