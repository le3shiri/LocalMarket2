# 07 — Sequence Diagram: Checkout and payment

```mermaid
sequenceDiagram
    actor C as Customer
    participant CC as CheckoutController
    participant OS as OrderService
    participant CS as CouponService
    participant IS as InventoryService
    participant PC as PaymentController
    participant PS as PaymentService
    participant ST as Stripe / Mock gateway
    participant WH as StripeWebhookController
    participant NS as NotificationService
    participant DB as Database

    C->>CC: POST /checkout (fulfilment, address, coupon)
    CC->>CC: validate input
    CC->>OS: place(user, cart, data)
    OS->>DB: BEGIN TRANSACTION
    OS->>CS: validate(code, items) [lock coupon row]
    CS-->>OS: coupon + discount
    loop each cart item
        OS->>IS: reserve(product, onlineLocation, qty)
        IS->>DB: SELECT stock_level FOR UPDATE
        alt available < qty
            IS-->>OS: InsufficientStockException
            OS->>DB: ROLLBACK
            OS-->>CC: error → back to cart
        else
            IS->>DB: reserved += qty, insert movement(reservation)
        end
    end
    OS->>DB: insert order (pending_payment), items (snapshots), status history
    OS->>DB: COMMIT
    CC-->>C: redirect /pago/{order}
    C->>PC: GET /pago/{order}
    PC->>PS: start(order)
    alt driver = stripe
        PS->>ST: create Checkout Session (amount in cents)
        ST-->>C: hosted card page
        ST->>WH: POST /webhooks/stripe (signed)
        WH->>WH: verify signature
        WH->>PS: markPaid(order, sessionId, eventId)
    else driver = mock (labelled test page)
        C->>PC: POST simulate approve / decline
        PC->>PS: markPaid / markFailed(order, ref, eventId)
    end
    PS->>DB: BEGIN; lock order; skip if event already processed
    alt paid
        PS->>IS: consumeReservation(order) (quantity −= qty, reserved −= qty)
        PS->>DB: order confirmed, payment paid, history
        PS->>NS: notify(order, "confirmed")
    else failed
        PS->>IS: releaseReservation(order)
        PS->>DB: order cancelled, payment failed, coupon use returned
    end
    PS->>DB: COMMIT
```
