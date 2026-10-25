# ADR-002 — Inventory model: stock levels + movement ledger with reservations

- **Status:** Accepted (policy values pending client approval)

## Context
Stock must be tracked per location, every change must be auditable, and simultaneous online
orders must never oversell.

## Decision
- `stock_levels` holds the **current** `quantity` and `reserved` per (product, location) — unique pair.
- `stock_movements` is an **append-only ledger**. Every change to a stock level writes one movement in the
  **same DB transaction**.
- All changes go through `App\Services\InventoryService`, which locks the stock row with
  `lockForUpdate()` (`SELECT … FOR UPDATE` on MySQL) before checking and updating it.
- Order placement **reserves**; payment **consumes**; failure/cancel/expiry **releases**.
- Perishable products also keep `batches` (lot + expiry). Outgoing stock consumes batches FEFO.

## Consistency rule
For each (product, location): `quantity = Σ movements.quantity where type affects quantity`
and `reserved = Σ reservation movements`. The test suite checks this invariant after operations.

| Movement type | Δ quantity | Δ reserved |
|---|---|---|
| receipt | + | 0 |
| adjustment | ± | 0 |
| transfer_out / transfer_in | − / + | 0 |
| store_sale | − | 0 |
| reservation | 0 | + |
| release | 0 | − |
| sale (online, on payment) | − | − |
| return (paid order cancelled) | + | 0 |

## Consequences
- Simple to understand and to audit. Balances are fast to read.
- Row locks serialise concurrent orders for the same product; MySQL InnoDB is required in production
  (SQLite serialises writes globally, which is also safe for tests).
