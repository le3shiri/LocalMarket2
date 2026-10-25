# 09 — State Diagram: Order lifecycle

```mermaid
stateDiagram-v2
    [*] --> pending_payment: order placed (stock reserved)
    pending_payment --> confirmed: payment verified [system]
    pending_payment --> cancelled: payment failed / expired / customer or admin cancels\n(release stock)
    confirmed --> preparing: admin, cashier
    preparing --> ready_for_pickup: [pickup] admin, cashier
    preparing --> out_for_delivery: [delivery] admin, cashier
    ready_for_pickup --> completed: collected
    out_for_delivery --> completed: delivered
    confirmed --> cancelled: admin (return stock + refund)
    preparing --> cancelled: admin (return stock + refund)
    ready_for_pickup --> cancelled: admin (return stock + refund)
    out_for_delivery --> cancelled: admin (return stock + refund)
    completed --> [*]
    cancelled --> [*]
```

Implemented in `App\Models\Order::TRANSITIONS` and `App\Services\OrderService::changeStatus()`.
Payment status (`pending → paid | failed`, `paid → refunded`) is stored separately in `orders.payment_status`.
