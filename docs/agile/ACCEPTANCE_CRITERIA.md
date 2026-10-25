# Acceptance Criteria

Written as Given / When / Then. Test references point to `tests/Feature/*`.

### US-01 Register & log in
- Given a visitor, when they register with name, email and a password (min 8, confirmed), then a **customer** account is created and they are logged in. *(AuthTest)*
- Given a deactivated user, when they log in with correct credentials, then login is refused with a Spanish message. *(AuthTest)*
- Passwords are stored hashed, never in plain text. *(AuthTest)*

### US-02 Password reset
- When a user requests a reset for an existing email, a reset link notification is sent; the response is the same for unknown emails (no account enumeration). *(AuthTest)*
- With a valid token the user sets a new password and can log in with it. *(AuthTest)*

### US-03 Role access
- Each staff route returns **403** for roles that are not allowed and **302 → login** for guests. *(PermissionTest)*

### US-04 / US-05 Catalogue
- Inactive products and products of inactive categories are not shown nor reachable by URL (404). *(CatalogTest)*
- Search by name and filter by category work. Product page shows price in `0,00 €` format and availability.

### US-06 Product management
- Admin can create/edit a product with validation (price ≥ 0, valid unit, category exists). Change is audited. *(ProductAdminTest)*

### US-07..US-10 Inventory
- Receipt increases quantity, creates a `receipt` movement with user & reason; for expiry products a batch is required. *(InventoryTest)*
- Transfer fails when source unreserved stock is insufficient and changes nothing. *(InventoryTest)*
- Adjustment cannot make quantity negative. *(InventoryTest)*
- Movements list is filterable by product, location and type.

### US-11 Alerts
- Dashboard lists products with available ≤ threshold at the online location and batches expiring in ≤ 3 days. *(DashboardTest)*

### US-12..US-14 Cart & checkout
- Cart quantities are validated (integers for unit/pack, multiples of 0,25 for kg) and limited by availability.
- Delivery requires address and a postcode in the service area; delivery fee applies below the free threshold. *(CheckoutTest)*
- On checkout, stock is reserved; if any item is short the order is not created and nothing is reserved. *(OversellingTest)*
- Order items keep name, unit and price even if the product changes later. *(CheckoutTest)*

### US-15 Payment
- Successful payment → order `confirmed`, payment `paid`, reservation consumed. *(PaymentTest)*
- Failed payment → order `cancelled`, payment `failed`, reservation released, coupon use returned. *(PaymentTest)*
- The same event processed twice has no additional effect. *(PaymentTest)*
- Stripe webhook with an invalid signature returns 400; a valid one confirms the order. *(PaymentTest)*

### US-16 / US-17 / US-18 Orders
- Customers only see their own orders (others → 403). *(OrderStatusTest)*
- Invalid transitions (e.g. pickup → out_for_delivery) are rejected. *(OrderStatusTest)*
- Only admin can cancel a paid order; this returns stock and marks payment `refunded`. *(OrderStatusTest)*
- Every transition is stored in order status history with user.

### US-19 Notifications
- An email is sent once per (order, event); repeated events do not resend; failures are logged as `failed`. *(NotificationTest)*

### US-20 Coupons
- Expired, not-yet-valid, inactive, over-limit and below-minimum coupons are rejected with a clear message. *(CouponTest)*
- Category coupons discount only eligible items; fixed discount never exceeds the eligible subtotal. *(CouponTest)*

### US-21 Accounts
- Admin can create employees with a role, deactivate them and send a reset link; admin cannot deactivate or demote themself. *(UserAdminTest)*

### US-22 Audit
- Product, stock, order, coupon and user changes create audit entries visible only to admin. *(PermissionTest, InventoryTest)*

### US-23 Expiry of unpaid orders
- `php artisan orders:expire-unpaid` cancels orders pending payment older than the timeout and releases stock. *(OrderStatusTest)*

### US-24 In-store sale
- Cashier can sell only at store locations and only available stock; a `store_sale` movement is recorded. *(InventoryTest)*
