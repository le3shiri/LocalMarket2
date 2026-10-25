# Risk Register

Probability/Impact: L / M / H.

| ID | Risk | P | I | Mitigation | Owner |
|---|---|---|---|---|---|
| R-01 | Deadline unknown; scope too large for two students. | H | H | MVP first; sprint plan can merge Sprints 5–6; keep coupons/POS optional. | Both |
| R-02 | Overselling under simultaneous orders. | M | H | Transactions + `lockForUpdate`, reservation policy, automated tests. | A |
| R-03 | Payment provider account/keys not available. | H | M | Labelled mock gateway; Stripe code path ready; document blocker. | A |
| R-04 | Client requirements change (delivery area, PIN, POS). | M | M | Assumptions configurable in `.env`; decision log. | Both |
| R-05 | Secrets committed to Git. | L | H | `.env` ignored; `.env.example` placeholders; review before push. | Both |
| R-06 | Uneven knowledge between developers. | M | M | Cross-reviews; rotate one story per sprint across areas. | Both |
| R-07 | Data loss in MySQL. | L | H | Documented `mysqldump` backups; migrations only, no manual schema edits. | A |
| R-08 | XAMPP MariaDB 10.4 differs from production MySQL 8. | L | M | Use standard Laravel schema builder; test with SQLite and MariaDB. | A |
| R-09 | Emails not delivered in production. | M | M | Notification log with failed status; configurable SMTP. | B |
| R-10 | GDPR / privacy of customer data. | M | H | Collect minimum data; no card data; retention question OQ-09. | Both |
