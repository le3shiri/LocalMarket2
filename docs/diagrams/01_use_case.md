# 01 — Use Case Diagram

Mermaid has no native UML use-case shape, so actors are drawn as nodes and use cases as rounded boxes.

```mermaid
flowchart LR
    Customer([👤 Customer])
    Cashier([👤 Store worker / Cashier])
    Warehouse([👤 Warehouse technician])
    Admin([👤 Administrator])
    Stripe([⚙️ Payment provider])
    Mail([⚙️ Email service])

    subgraph LocalMarket
        UC1(Browse catalogue / search)
        UC2(Manage cart)
        UC3(Checkout: delivery or pickup)
        UC4(Apply coupon)
        UC5(Pay by card)
        UC6(View my orders)
        UC7(Cancel unpaid order)
        UC8(Register / log in / reset password)
        UC10(Record receipt / transfer / adjustment)
        UC11(View stock by location & movements)
        UC12(Manage batches & expiry alerts)
        UC13(Update order status)
        UC14(Record in-store sale *pending approval*)
        UC15(Manage products & categories)
        UC16(Manage coupons)
        UC17(Manage employees & customers)
        UC18(Cancel paid order / refund)
        UC19(View audit log)
        UC20(Send order notifications)
    end

    Customer --> UC1 & UC2 & UC3 & UC4 & UC5 & UC6 & UC7 & UC8
    UC5 --- Stripe
    Warehouse --> UC10 & UC11 & UC12
    Cashier --> UC11 & UC13 & UC14
    Admin --> UC11 & UC12 & UC13 & UC15 & UC16 & UC17 & UC18 & UC19
    UC20 --- Mail
    UC13 -.include.-> UC20
    UC5 -.include.-> UC20
```

**Notes**
- All staff use cases require login; the server checks the role on every request (`role` middleware).
- UC14 is implemented in a simple form and is flagged as *pending client approval* (OQ-02).
