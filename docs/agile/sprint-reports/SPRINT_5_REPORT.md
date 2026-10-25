# Sprint 5 Report — LocalMarket

**Sprint Goal:** Implement the customer payment processing module supporting both a test-mode / simulated mock POS gateway and Stripe card payments, handle asynchronous payment webhooks idempotently, automate customer notification emails (in Spanish) on order confirmation and status changes, and build the automated order expiry command (`orders:expire-unpaid`) to return reserved stock.

**Dates:** Sprint 5  
**Status:** Completed  
**Branch:** `sprint/5-payments-and-notifications`  
**Git Tag:** `sprint-5`  

---

## 1. Summary of Deliverables

### Database & Migrations
- `database/migrations/2026_10_25_001000_create_payments_table.php`:
  - `payments`: Foreign key `order_id`, provider (`mock` or `stripe`), `transaction_id`, `status` (`pending`, `completed`, `failed`, `refunded`), `amount`, `payment_method`, `card_last4`, `card_brand`, `payload` (JSON audit), `completed_at`, `failed_at`.

### Domain Models & Logic
- `app/Models/Payment.php`: Payment Eloquent model with status constants, labels, casts, and relationship to `Order`.
- `app/Models/Order.php`: Updated with `payments()` and `latestPayment()` relationships.
- `app/Services/PaymentService.php`:
  - `processMockPayment()`: Atomic transaction processing simulated cards with success/failure toggles for grading and demonstration.
  - `createStripePaymentIntent()`: Integration with Stripe's PaymentIntents API.
  - `handleStripeWebhook()`: Idempotent processing of `payment_intent.succeeded` and `payment_intent.payment_failed`.
  - `recordSuccessfulPayment()`: Consumes the reserved stock (`quantity -= qty, reserved -= qty`), updates order to `confirmed` and `paid`, records status history, audit log, and dispatches the confirmation email.
  - `recordFailedPayment()`: Logs failure without corrupting or releasing stock reservation prematurely.
- `app/Services/NotificationService.php`:
  - `sendOrderConfirmation()`: Sends detailed order confirmation email in Spanish with pickup/delivery details.
  - `sendStatusUpdate()`: Sends notifications upon status changes (`ready_for_pickup`, `out_for_delivery`, `completed`, `cancelled`).
- `app/Console/Commands/ExpireUnpaidOrdersCommand.php`:
  - Artisan command `php artisan orders:expire-unpaid {--minutes=30}` that queries stale orders in `pending_payment`, marks them `cancelled`, releases reserved stock back to available inventory, logs audit records, and notifies the customer.

### Controllers, Views & Routes
- `app/Http/Controllers/PaymentController.php`: Handles payment form rendering (`payment.show`), mock submission, and Stripe intent initialization.
- `app/Http/Controllers/StripeWebhookController.php`: Webhook listener with CSRF exemption in `bootstrap/app.php`.
- `resources/views/payment/show.blade.php`: User-friendly payment screen featuring card inputs, QA outcome toggles (simular éxito / fallo), live order summary, and temporary stock reservation timeout warning.
- `resources/views/account/orders/show.blade.php`: Added "Proceder al Pago Ahora" action button when an order is in pending payment.
- `routes/web.php`: Registered payment and webhook endpoints.

---

## 2. Testing & Quality Assurance

- **Payment Flow & Webhook Verification (`tests/Feature/PaymentTest.php`):**
  - Customer access to payment screen.
  - Successful mock payment confirming order and consuming stock reservation.
  - Failed mock payment keeping order in pending status and logging error.
  - Stripe webhook `payment_intent.succeeded` verification and idempotency check.
  - Artisan `orders:expire-unpaid` command canceling stale orders and releasing reserved stock back to 0.

### Test Execution Summary
- **Total Test Cases:** 50 tests across 9 test suites.
- **Total Assertions:** 193 assertions.
- **Failures:** 0.
- **Code Style (Laravel Pint):** 100% compliant.

---

## 3. Sprint Sign-Off
- **Developer A & Developer B Sign-off:** Approved.
- **Next Sprint:** Sprint 6 — Admin Management (Employee RBAC & Audit Log Inspection) & Promotions (Coupons Engine).
