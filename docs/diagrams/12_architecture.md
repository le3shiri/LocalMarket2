# 12 — Architecture Diagram

```mermaid
flowchart TB
    subgraph Browser
        UI[Server-rendered HTML pages<br/>Spanish UI, responsive CSS]
    end
    subgraph Laravel["Laravel 12 application (monolith)"]
        direction TB
        RT[Routes + Middleware<br/>web, auth, role:*, CSRF]
        CT[Controllers<br/>thin: validate → call service → view/redirect]
        SV[Services<br/>business rules + DB transactions]
        MD[Eloquent Models]
        VW[Blade views + layouts<br/>shop / staff]
        SCH[Scheduler<br/>orders:expire-unpaid every minute]
        ML[Mail: OrderUpdateMail]
    end
    DB[(MySQL / MariaDB)]
    EXT[Stripe API]

    UI <--> RT --> CT --> SV --> MD --> DB
    CT --> VW --> UI
    SV --> ML
    SV <--> EXT
    SCH --> SV
```

**Layers & rules**
1. Controllers never update stock or order status directly — they call a service.
2. Services wrap multi-step changes in `DB::transaction()` and lock rows they modify.
3. Views only format data (`eur()`, `qty()` helpers in `app/helpers.php`).
