# Assumptions and Open Questions

Status values: **Open** (needs client answer), **Proposed** (default implemented, awaiting approval), **Confirmed**.

## Assumptions (implemented as defaults — easy to change)
| ID | Assumption | Where to change | Status |
|---|---|---|---|
| AS-01 | Online orders are served from a single location (Tienda Barcelona). | `locations.is_online_source` | Proposed |
| AS-02 | Delivery fee 4,95 €; free from 50 €. | `.env` `DELIVERY_FEE`, `FREE_DELIVERY_FROM` | Proposed |
| AS-03 | Delivery area = Barcelona city postcodes 08001–08042. | `.env` `DELIVERY_POSTCODES` | Proposed |
| AS-04 | Unpaid orders expire after 30 minutes. | `.env` `ORDER_PAYMENT_TIMEOUT_MINUTES` | Proposed |
| AS-05 | Sale units: unit, kg, package. Kg sold in steps of 0,25 kg. | `App\Models\Product::UNITS` | Proposed |
| AS-06 | Prices include VAT (IVA). No invoice generation in MVP. | — | Proposed |
| AS-07 | One coupon per order, no stacking. | `CouponService` | Proposed |
| AS-08 | Low-stock threshold per product, default 5. Near-expiry = 3 days. | product form / `config/localmarket.php` | Proposed |
| AS-09 | Stripe Checkout (test mode) is the payment provider; a labelled mock is used until keys exist. | `.env` `PAYMENT_DRIVER` | Proposed |
| AS-10 | Email is the only notification channel; dev uses the `log` mailer. | `.env` `MAIL_*` | Proposed |
| AS-11 | Customers can cancel only orders still pending payment. | `OrderStatus` rules | Proposed |
| AS-12 | Weight-based products are charged at the ordered weight (no re-weighing price adjustment). | — | Proposed |

## Open questions (need client decision)
| ID | Question | Impact |
|---|---|---|
| OQ-01 | Should employees log in with a PIN in the store (in addition to email/password)? | Not implemented. |
| OQ-02 | Is recording physical (in-store) sales by cashiers in scope? A simple version is implemented and can be hidden. | FR-POS-01 |
| OQ-03 | Which payment provider and account will be used in production (Stripe?) and who owns it? | Live payments blocked. |
| OQ-04 | Real delivery area, fees, time slots and delivery days? | Delivery config. |
| OQ-05 | Is a courier company used, or only own staff? | Courier integration excluded. |
| OQ-06 | How should weight items be handled if the final weight differs from the ordered weight? | Pricing/refunds. |
| OQ-07 | Does the business need VAT invoices (factura) for customers? | Not in MVP. |
| OQ-08 | Should online orders be able to draw stock from the warehouse too? | BR-INV-03. |
| OQ-09 | Data retention period for customer data and audit logs (GDPR)? | Privacy policy. |
| OQ-10 | Production hosting and domain? | Deployment. |
| OQ-11 | Real project start date, sprint dates and final deadline? | Sprint plan. |
| OQ-12 | Which email address/provider sends notifications? | Notifications in production. |
