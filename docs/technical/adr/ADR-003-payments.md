# ADR-003 — Payments: Stripe Checkout (test mode) with a labelled mock fallback

- **Status:** Accepted for development. Production provider is an open question (OQ-03).

## Decision
- `PAYMENT_DRIVER=mock` (default): a page clearly labelled **"PASARELA DE PRUEBA — no se realiza ningún cargo"**
  lets the developer simulate an approved or declined payment. It only exists when the driver is `mock`.
- `PAYMENT_DRIVER=stripe`: the customer is redirected to **Stripe Checkout** (hosted page, so card data never
  touches LocalMarket). The order is confirmed **only** by the signed webhook `checkout.session.completed`,
  verified with `STRIPE_WEBHOOK_SECRET`. The success page does not confirm anything.
- Both drivers call the same `PaymentService::markPaid()` / `markFailed()` so stock and status logic is shared.
- Idempotency: `payments.event_id` is unique, and a payment already `paid` is never processed again.

## Consequences
- The full order flow can be demonstrated without credentials and without pretending money moved.
- Real Stripe behaviour requires test keys + `stripe listen` (Stripe CLI). Webhook signature verification is
  covered by automated tests using a locally signed payload.
