# Sprint Plan (provisional)

- **Sprint length:** 2 weeks (default). Reason: two students with other courses; 1-week sprints leave too
  little time per story after ceremonies. Switch to 1 week if the deadline is short.
- **Capacity assumption:** ~10 h/week per developer → ~20 story points per sprint for the team,
  with **20 % contingency** kept free.
- **Estimation:** story points (Fibonacci 1, 2, 3, 5, 8). 1 point ≈ half a day of focused work.
- **Dates:** real dates are unknown (OQ-11). Use relative weeks; fill the calendar table in `PROJECT_TIMELINE.md`.
- **Owners:** A = Developer A (backend/DB/auth/inventory), B = Developer B (frontend/UI/catalogue/cart). Both review.

| Sprint | Weeks | Goal | Stories (owner) | Pts | Demo criteria |
|---|---|---|---|---|---|
| 0 | W0 | Discovery & setup | Requirements, diagrams, backlog, repo, CI (A+B) | — | Docs reviewed; repo runs `php artisan test`. |
| 1 | W1–W2 | Foundation | US-01 (B), US-02 (A), US-03 (A); base layouts (B) | 8 | Login per role; guest/403 checks. |
| 2 | W3–W4 | Catalogue | US-04 (B), US-05 (B), US-06 (A) | 10 | Browse, search, admin edits product. |
| 3 | W5–W6 | Inventory | US-07 (A), US-08 (A), US-09 (A), US-10 (B), US-11 (B), US-24 (B) | 17 | Receipt → transfer → availability updated. |
| 4 | W7–W8 | Cart & orders | US-12 (B), US-13 (B), US-14 (A), US-16 (B), US-17 (A) | 18 | Order placed, stock reserved, overselling blocked. |
| 5 | W9–W10 | Payments & fulfilment | US-15 (A), US-18 (A), US-19 (B), US-23 (A) | 16 | Mock/Stripe payment confirms; failure releases. |
| 6 | W11–W12 | Admin & promotions | US-20 (B), US-21 (A), US-22 (A) | 11 | Coupon applied; employee created; audit visible. |
| 7 | W13–W14 | Stabilisation & delivery | E2E review, accessibility, security review, docs, demo (A+B) | — | Final demo script runs end-to-end. |

## Per-sprint checklist
1. Planning: pick stories that meet the Definition of Ready.
2. Create `sprint/N-…` branch from `develop`.
3. Daily stand-up (template in `DAILY_STANDUP_TEMPLATE.md`), async allowed.
4. Each story: code + tests + docs → PR → review by the other developer.
5. Sprint end: run `vendor/bin/pint --test` and `php artisan test`, update docs, push, PR, merge, tag `sprint-N`, write `sprint-reports/SPRINT_N_REPORT.md`.

## Dependencies
- Sprint 3 needs products (Sprint 2). Sprint 4 needs inventory (Sprint 3). Sprint 5 needs orders (Sprint 4).
- Stripe test keys (OQ-03) needed to demo real Stripe in Sprint 5 — mock gateway is the fallback.

## Risks & contingency
See `RISK_REGISTER.md`. If behind schedule, merge Sprint 6 into 5 and keep coupons simple; never cut tests for inventory/payments.
