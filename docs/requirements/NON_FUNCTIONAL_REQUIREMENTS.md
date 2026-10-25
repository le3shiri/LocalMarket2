# Non-Functional Requirements

| ID | Category | Requirement |
|---|---|---|
| NFR-01 | Usability | Spanish UI, responsive (mobile ≥ 360px), clear validation and empty states. |
| NFR-02 | Localisation | EUR format `1.234,56 €`, dates `dd/mm/yyyy HH:mm`, timezone Europe/Madrid. |
| NFR-03 | Security | bcrypt password hashing, CSRF protection, server-side authorization, Eloquent (parameterised) queries, Blade auto-escaping. |
| NFR-04 | Security | Secrets only in `.env` (never committed). `.env.example` contains placeholders. |
| NFR-05 | Privacy | Only data needed for orders is collected (name, email, phone, address). No card data stored. Logs never contain passwords or card data. |
| NFR-06 | Integrity | Stock and order changes run in DB transactions; stock rows locked with `SELECT … FOR UPDATE`. |
| NFR-07 | Auditability | Stock movements, order status history and audit log are append-only from the UI. |
| NFR-08 | Maintainability | Plain Laravel conventions (controllers, models, a few services). No SPA framework. |
| NFR-09 | Testability | Automated PHPUnit feature tests run with `php artisan test` on SQLite in-memory. |
| NFR-10 | Performance | Catalogue pages paginated (12 per page); indexes on foreign keys and lookup columns. |
| NFR-11 | Accessibility | Labels on all inputs, sufficient colour contrast, keyboard-usable forms, `alt` text on images. |
| NFR-12 | Portability | Runs on Windows (XAMPP) and Linux/macOS with PHP 8.2+, Composer and MySQL/MariaDB. |
| NFR-13 | Recoverability | Documented database backup/restore procedure (`mysqldump`). |
