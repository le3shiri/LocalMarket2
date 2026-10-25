# 08 — Sequence Diagram: Stock update (transfer)

```mermaid
sequenceDiagram
    actor W as Warehouse technician
    participant IC as InventoryController
    participant IS as InventoryService
    participant DB as Database
    participant AL as AuditLog

    W->>IC: POST /gestion/inventario/traspaso (product, from, to, qty, reason)
    IC->>IC: role:warehouse + validate (from ≠ to, qty > 0)
    IC->>IS: transfer(product, from, to, qty, user, reason)
    IS->>DB: BEGIN TRANSACTION
    IS->>DB: SELECT stock_level(from) FOR UPDATE
    IS->>DB: SELECT stock_level(to) FOR UPDATE (create if missing)
    alt quantity − reserved < qty
        IS->>DB: ROLLBACK
        IS-->>IC: InsufficientStockException
        IC-->>W: validation error (Spanish)
    else enough
        IS->>DB: from.quantity −= qty; movement transfer_out
        IS->>DB: to.quantity += qty; movement transfer_in
        opt product tracks expiry
            IS->>DB: consume source batches FEFO, create destination batches (same lot/expiry)
        end
        IS->>DB: COMMIT
        IC->>AL: record("stock.transfer", product, details)
        IC-->>W: success message
    end
```
