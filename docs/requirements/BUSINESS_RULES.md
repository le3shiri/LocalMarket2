# Business Rules

> Rules marked **(A)** are assumptions/proposals awaiting client approval — see `ASSUMPTIONS_AND_OPEN_QUESTIONS.md`.

## Inventory
- **BR-INV-01** Stock is held per (product, location). Quantity can never go below 0.
- **BR-INV-02** Every change of quantity or reservation creates a `stock_movements` row (product, location, type, quantity, reason, user, order, timestamp). Movements are never edited or deleted from the UI.
- **BR-INV-03 (A)** Online orders are fulfilled from **one** location flagged `is_online_source` (default: *Tienda Barcelona*).
- **BR-INV-04** Online available = `quantity − reserved` at the online source location.
- **BR-INV-05** Placing an order **reserves** stock (`reserved += qty`) inside a transaction with row locks. If any item lacks stock, the whole order is rejected.
- **BR-INV-06** Payment success **consumes** the reservation: `quantity −= qty`, `reserved −= qty` (movement `sale`).
- **BR-INV-07** Payment failure, customer cancellation or expiry of an unpaid order **releases** the reservation (`reserved −= qty`, movement `release`).
- **BR-INV-08** Admin cancellation of a **paid** order returns stock to the online location (movement `return`) and marks payment `refunded`.
- **BR-INV-09** Transfers create two movements (`transfer_out` at source, `transfer_in` at destination). Source must have enough *unreserved* stock.
- **BR-INV-10** For products with `tracks_expiry`, receipts require a lot code and expiry date. Outgoing stock consumes batches **FEFO** (first-expired, first-out).
- **BR-INV-11 (A)** Low stock alert when available ≤ product `low_stock_threshold` (default 5). Near-expiry alert when a batch with remaining quantity expires within 3 days.
- **BR-INV-12** Cashiers may only record sales at **store** locations; they cannot receive, transfer or adjust stock.

## Orders
- **BR-ORD-01** Order number format: `LM-YYYYMMDD-XXXXX` (unique).
- **BR-ORD-02** Order items store a snapshot of product name, sale unit and unit price.
- **BR-ORD-03** Totals: `total = subtotal − discount + delivery_fee`, all rounded to 2 decimals (half up).
- **BR-ORD-04** Status transitions (see `docs/diagrams/09_state_order_lifecycle.md`):

| From | To | Who | Condition |
|---|---|---|---|
| pending_payment | confirmed | System (payment) | Payment verified |
| pending_payment | cancelled | Customer, Admin, System | Payment failed / expired / cancelled |
| confirmed | preparing | Admin, Cashier | — |
| preparing | ready_for_pickup | Admin, Cashier | Pickup orders only |
| preparing | out_for_delivery | Admin, Cashier | Delivery orders only |
| ready_for_pickup | completed | Admin, Cashier | Customer collected |
| out_for_delivery | completed | Admin, Cashier | Delivered |
| confirmed / preparing / ready_for_pickup / out_for_delivery | cancelled | Admin only | Triggers return of stock + refund |

- **BR-ORD-05 (A)** Unpaid orders expire after **30 minutes** (`ORDER_PAYMENT_TIMEOUT_MINUTES`).
- **BR-ORD-06** Completed and cancelled are final states.

## Payments
- **BR-PAY-01** Payment statuses: `pending`, `paid`, `failed`, `refunded`. Independent of order status.
- **BR-PAY-02** A payment event ID is processed only once (unique constraint).
- **BR-PAY-03** A payment for an order that is no longer `pending_payment` (e.g. expired) is recorded and flagged for manual refund.

## Delivery
- **BR-DEL-01 (A)** Delivery fee **4,95 €**, free for subtotal (after discount) ≥ **50 €**.
- **BR-DEL-02 (A)** Service area: Barcelona postcodes `08001`–`08042` (configurable list of prefixes).
- **BR-DEL-03** Pickup orders have no delivery fee.

## Coupons
- **BR-COU-01** Code is case-insensitive and unique.
- **BR-COU-02** Valid only if active, `starts_at ≤ now ≤ ends_at` (Europe/Madrid), usage limit not reached, subtotal ≥ minimum.
- **BR-COU-03** If a category is set, the discount applies only to items of that category.
- **BR-COU-04** Percent discount = eligible subtotal × value / 100. Fixed discount = min(value, eligible subtotal). Rounded to 2 decimals.
- **BR-COU-05** One coupon per order (no stacking).
- **BR-COU-06** Usage is counted when the order is placed and given back if the order is cancelled/expired **before** payment.

## Accounts
- **BR-ACC-01** Roles: `admin`, `warehouse`, `cashier`, `customer`. One role per user.
- **BR-ACC-02** Only admins create employee accounts. Public registration always creates customers.
- **BR-ACC-03** An admin cannot deactivate or demote their own account.
