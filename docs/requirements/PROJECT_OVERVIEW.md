# LocalMarket — Project Overview

## 1. Context
LocalMarket is a supermarket / grocery business in **Barcelona, Spain** (currency **EUR**, timezone **Europe/Madrid**).
Today, products, stock and business data are partly managed in **Excel**. This causes:

- Accidental modifications with no change tracking.
- Stock inconsistencies between the warehouse, the physical store and online sales.
- Difficulty coordinating online and in-store sales.

## 2. Objective
Build a user-friendly, secure and maintainable **web application** to manage:
the product catalogue, inventory (warehouse + store), customer orders, online card payments,
promotions/coupons, employees, and home delivery or store pickup.

## 3. Team & method
- Two student developers (**Developer A**, **Developer B**).
- Agile, Scrum-inspired sprints (see `docs/agile/`).

## 4. Scope (MVP)
| In scope | Out of scope (until approved) |
|---|---|
| Customer catalogue, cart, checkout, order history | Third-party courier integration |
| Delivery / pickup selection with configurable fee & area | Real (live) card payments |
| Card payment through a provider (Stripe test mode) or a labelled **mock** | Mobile native apps |
| Inventory by location, stock movements, batches/expiry | Accounting / invoicing (VAT invoices) |
| Order lifecycle with role-based status changes | Employee PIN login (open question) |
| Coupons with dates, limits, minimum order, category | Loyalty programme |
| Employee accounts & roles, password reset | Multi-store chains |
| Audit log, order status history | SMS / push notifications |
| Email notifications (log mailer in development) | |
| Simple in-store sale recording by cashier (**pending approval**) | |

## 5. Languages
- Customer & staff interface: **Spanish**.
- Technical documentation: **English**.
- Code, identifiers and database names: **English**.

## 6. Technology (summary)
Laravel 12 (PHP 8.2) + Blade templates + plain CSS, MySQL/MariaDB (XAMPP) for development,
SQLite in-memory for automated tests. See `docs/technical/adr/ADR-001-stack.md`.
