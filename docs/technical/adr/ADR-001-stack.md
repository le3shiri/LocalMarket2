# ADR-001 — Technology stack

- **Status:** Accepted (by the team request "Laravel, simple, no complicated code")
- **Date:** 2026-10-25

## Context
Two student developers must build and maintain a full-stack web application (catalogue, inventory,
orders, payments, roles). The local machines run Windows with XAMPP (PHP 8.2, MariaDB 10.4).
The team explicitly asked for **Laravel** with **simple code**.

## Options considered
| Option | Pros | Cons |
|---|---|---|
| **Laravel + Blade (server-rendered)** | One language (PHP), built-in auth helpers, migrations, validation, mail, scheduler, testing. Very easy to run on XAMPP. | Less "app-like" interactivity than an SPA. |
| Laravel API + React/Vue SPA | Rich UI | Two codebases, build tooling, CORS/auth tokens — too complex for the team. |
| Node (Express/Next.js) + Prisma | Modern JS | Team asked for Laravel; more decisions to make. |
| Laravel + Livewire/Inertia | Interactivity | Extra concepts to learn. |

## Decision
- **Laravel 12** (PHP 8.2+), **Blade** templates, **plain CSS** in `public/css/app.css` and a tiny
  `public/js/app.js`. **No Node/Vite build step** is required.
- **MySQL/MariaDB** (XAMPP) for development; **SQLite in-memory** for automated tests.
- Hand-written, minimal auth controllers (no starter-kit) using Laravel's `Auth`, `Hash` and `Password` broker.
- Roles as a single `role` column + a small `role` middleware (no permissions package).
- Business logic in a few plain service classes (`app/Services`): Inventory, Cart, Coupon, Order, Payment, Notification.
- **Stripe Checkout** (test mode) via `stripe/stripe-php` when keys exist; otherwise a clearly labelled **mock** gateway.

## Consequences
- Easy to install (`composer install`, `php artisan migrate --seed`, `php artisan serve`).
- All rules are enforced server-side, which matches the security requirements.
- If richer interactivity is needed later, Livewire or Alpine.js can be added without a rewrite.
