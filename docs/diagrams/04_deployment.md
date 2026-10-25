# 04 — UML Deployment Diagram

```mermaid
flowchart TB
    subgraph Dev["Developer PC (Windows + XAMPP)"]
        PHPd["php artisan serve :8000<br/>PHP 8.2"]
        SCH["php artisan schedule:work<br/>(expires unpaid orders)"]
        MYd[("MariaDB 10.4<br/>db: localmarket")]
        PHPd --> MYd
        SCH --> MYd
    end

    subgraph Prod["Proposed production (OQ-10, not deployed)"]
        NG["Nginx / Apache + HTTPS"]
        FPM["PHP-FPM 8.2<br/>Laravel app"]
        CRON["cron: * * * * * php artisan schedule:run"]
        MYp[("MySQL 8")]
        FS[/"storage/app/public<br/>product images"/]
        NG --> FPM --> MYp
        FPM --> FS
        CRON --> FPM
    end

    Browser((Browser)) --> NG
    Browser -.-> PHPd
    StripeCloud["Stripe API"] <--> FPM
    SMTP["SMTP provider"] <-- FPM
```

**Notes:** No production deployment exists yet. See `docs/delivery/DEPLOYMENT_GUIDE.md`.
