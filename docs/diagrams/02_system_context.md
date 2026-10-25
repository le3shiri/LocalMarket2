# 02 — System Context Diagram

```mermaid
flowchart TB
    C[Customer<br/>web browser] -->|HTTPS: browse, order, pay| LM
    S[Staff: admin, warehouse, cashier<br/>web browser] -->|HTTPS: manage| LM
    LM[[LocalMarket web application<br/>Laravel 12]]
    LM -->|SQL| DB[(MySQL / MariaDB)]
    LM -->|Create Checkout Session, refunds| ST[Stripe - test mode<br/>optional]
    ST -->|Signed webhook| LM
    LM -->|SMTP or log mailer| EM[Email service]
    EM -->|Order emails| C
```

**Notes**
- Stripe is used only when `PAYMENT_DRIVER=stripe`; otherwise the internal mock gateway is used.
- In development the mailer is `log` (emails are written to `storage/logs/laravel.log`).
