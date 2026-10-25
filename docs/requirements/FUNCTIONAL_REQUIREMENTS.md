# Functional Requirements

Priority: **M** = Must (MVP), **S** = Should, **C** = Could. Status is tracked in `TRACEABILITY_MATRIX.md`.

## FR-AUTH — Accounts and security
| ID | Requirement | Priority |
|---|---|---|
| FR-AUTH-01 | Users log in with email and password. | M |
| FR-AUTH-02 | Customers can self-register. Employees are created only by an Administrator. | M |
| FR-AUTH-03 | Passwords are stored only as secure hashes (bcrypt). | M |
| FR-AUTH-04 | Users can reset their password through a signed, expiring email link. | M |
| FR-AUTH-05 | An Administrator can trigger a password-reset email for any user. | M |
| FR-AUTH-06 | Access is controlled by role on the server (admin, warehouse, cashier, customer). | M |
| FR-AUTH-07 | Deactivated accounts cannot log in. | M |

## FR-CAT — Product catalogue
| ID | Requirement | Priority |
|---|---|---|
| FR-CAT-01 | Products have name, description, image, category, price (EUR), sale unit, location info, active flag. | M |
| FR-CAT-02 | Sale unit is one of: unit, kilogram, package (configurable list in code). | M |
| FR-CAT-03 | Customers browse active products, filter by category and search by name. | M |
| FR-CAT-04 | Customers see product details and online availability. | M |
| FR-CAT-05 | Admin manages products and categories (create, edit, activate/deactivate). | M |
| FR-CAT-06 | Admin uploads a product image. | S |

## FR-INV — Inventory
| ID | Requirement | Priority |
|---|---|---|
| FR-INV-01 | Stock is tracked per product **and** per location (warehouse, store). | M |
| FR-INV-02 | Warehouse records receipts, transfers between locations and adjustments. | M |
| FR-INV-03 | Every stock change creates a movement with product, location, quantity, timestamp, user and reason. | M |
| FR-INV-04 | Perishable products track batches/lots with expiry dates. | M |
| FR-INV-05 | Online available stock = quantity − reserved at the online fulfilment location. | M |
| FR-INV-06 | Overselling is prevented by server-side validation inside a DB transaction with row locks. | M |
| FR-INV-07 | Stock is reserved when an order is placed and released if unpaid/cancelled. | M |
| FR-INV-08 | Dashboard shows low-stock and near-expiry alerts. | S |
| FR-INV-09 | Staff view stock movement history with filters. | M |

## FR-ORD — Cart and orders
| ID | Requirement | Priority |
|---|---|---|
| FR-ORD-01 | Customers add, update and remove products in a cart. | M |
| FR-ORD-02 | Checkout validates stock, coupon and fulfilment data on the server. | M |
| FR-ORD-03 | Order stores number, customer, items with price snapshots, subtotal, discount, delivery fee, total, payment status, fulfilment method, address, status, timestamps. | M |
| FR-ORD-04 | Order status follows the documented state machine (separate pickup/delivery paths). | M |
| FR-ORD-05 | Every status change is recorded in an order status history. | M |
| FR-ORD-06 | Customers see their order history and order details. | M |
| FR-ORD-07 | Staff see and manage orders according to role. | M |
| FR-ORD-08 | Unpaid orders expire after a configurable time and release stock. | S |

## FR-PAY — Payments
| ID | Requirement | Priority |
|---|---|---|
| FR-PAY-01 | Card payments via a provider (Stripe Checkout, test mode) or a clearly labelled mock. | M |
| FR-PAY-02 | Payment result is verified on the server (signed webhook for Stripe). | M |
| FR-PAY-03 | Duplicate payment events are ignored safely (idempotency). | M |
| FR-PAY-04 | No card numbers/CVC are stored by LocalMarket. | M |
| FR-PAY-05 | Payment status is kept separate from order (fulfilment) status. | M |
| FR-PAY-06 | Paid orders cancelled by admin are marked for refund (mock refund or provider refund). | S |

## FR-DEL — Delivery & pickup
| ID | Requirement | Priority |
|---|---|---|
| FR-DEL-01 | Customer chooses home delivery or store pickup. | M |
| FR-DEL-02 | Delivery fee, free-delivery threshold and service area (postcodes) are configurable. | M |
| FR-DEL-03 | Manual fulfilment workflow (no courier integration). | M |

## FR-PRO — Coupons & promotions
| ID | Requirement | Priority |
|---|---|---|
| FR-PRO-01 | Admin manages coupons: code, description, type (percent/fixed), value, dates, active flag, min order, category, usage limit. | M |
| FR-PRO-02 | Coupon eligibility and discount are calculated on the server. | M |
| FR-PRO-03 | One coupon per order (no stacking). | M |

## FR-USR — Employee & customer management
| ID | Requirement | Priority |
|---|---|---|
| FR-USR-01 | Admin creates, edits, activates/deactivates employee and customer accounts. | M |
| FR-USR-02 | Admin cannot remove their own admin role or deactivate themselves. | M |

## FR-NOT — Notifications
| ID | Requirement | Priority |
|---|---|---|
| FR-NOT-01 | Customers receive an email on meaningful order events (confirmed, ready, out for delivery, completed, cancelled). | M |
| FR-NOT-02 | Each (order, event) is notified at most once; failures are logged. | M |

## FR-AUD — Audit
| ID | Requirement | Priority |
|---|---|---|
| FR-AUD-01 | Important operations (products, stock, orders, coupons, users) create an audit record with user, action, subject, details, IP and time. | M |
| FR-AUD-02 | Admin can view and filter audit records. | M |

## FR-POS — In-store sales (pending client approval)
| ID | Requirement | Priority |
|---|---|---|
| FR-POS-01 | Cashier records a physical sale at a store location, which decreases store stock. | C |
