# Decision Log

Status: **Confirmed** (by the team/user), **Proposed** (default awaiting client approval).

| ID | Date | Decision | Reason | Decided by | Status |
|---|---|---|---|---|---|
| D-01 | 2026-10-25 | Use Laravel with simple code (Blade, no SPA, no build step). | Team request "Laravel simple no complicated code". | Team (user request) | Confirmed |
| D-02 | 2026-10-25 | MySQL/MariaDB via XAMPP for development, SQLite in-memory for tests. | User choice; fast tests. | Team (user choice) | Confirmed |
| D-03 | 2026-10-25 | Work locally with Git; GitHub remote added later. | No repository yet. | Team (user choice) | Confirmed |
| D-04 | 2026-10-25 | Team members named Developer A and Developer B in docs. | Names not provided. | Team (user choice) | Confirmed |
| D-05 | 2026-10-25 | Branching: `main` (stable), `develop` (integration), `sprint/N-*` branches, tags `sprint-N`. | Simple for two people. | AI assistant | Proposed |
| D-06 | 2026-10-25 | Single online source location (store) for online orders. | Simplest correct rule. | AI assistant | Proposed (AS-01) |
| D-07 | 2026-10-25 | Reserve on order placement, consume on payment, release on failure/expiry (30 min). | Prevent overselling without locking stock forever. | AI assistant | Proposed |
| D-08 | 2026-10-25 | Stripe Checkout test mode + labelled mock fallback. | No credentials available. | AI assistant | Proposed (OQ-03) |
| D-09 | 2026-10-25 | Role column + middleware, no permissions package. | Four fixed roles. | AI assistant | Proposed |
| D-10 | 2026-10-25 | Delivery details stored on the order row (no separate table). | One address per order; simpler. | AI assistant | Proposed |
| D-11 | 2026-10-25 | Coupon usage counted via `coupons.used_count` + `orders.coupon_id` (no coupon_usages table). | Per-order history already in orders. | AI assistant | Proposed |
| D-12 | 2026-10-25 | Admin has read-only inventory; only warehouse records receipts/transfers/adjustments. | Matches role description. | AI assistant | Proposed |
| D-13 | 2026-10-25 | Simple in-store sale page for cashiers implemented but flagged. | Helps keep online stock correct. | AI assistant | Proposed (OQ-02) |
| D-14 | 2026-10-25 | Emails sent synchronously (no queue worker). | Simpler to run for students. | AI assistant | Proposed |
