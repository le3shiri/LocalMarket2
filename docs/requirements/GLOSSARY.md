# Glossary

| Term (EN) | Spanish UI | Meaning |
|---|---|---|
| Product | Producto | An item sold by LocalMarket. |
| Category | Categoría | Group of products (e.g. Frutas y verduras). |
| Sale unit | Unidad de venta | How a product is sold: `unit` (ud.), `kg`, `pack` (paquete). |
| Location | Ubicación | A place that holds stock: warehouse (almacén) or store (tienda). |
| Online source location | Ubicación online | The location whose stock is sold online. |
| Stock level | Existencias | Quantity of a product at a location, plus the reserved part. |
| Reserved | Reservado | Quantity held by unpaid orders; not available to other customers. |
| Available | Disponible | `quantity − reserved`. |
| Stock movement | Movimiento de stock | Immutable record of a change in stock or reservation. |
| Receipt | Entrada | Goods received from a supplier. |
| Transfer | Traspaso | Moving goods between two locations. |
| Adjustment | Ajuste | Correction after counting, breakage, loss or expiry. |
| Batch / lot | Lote | A group of units of a perishable product sharing an expiry date. |
| FEFO | FEFO | First-Expired, First-Out consumption order for batches. |
| Order | Pedido | A customer purchase. |
| Fulfilment method | Tipo de entrega | `delivery` (envío a domicilio) or `pickup` (recogida en tienda). |
| Order status | Estado del pedido | Fulfilment progress (pending_payment … completed/cancelled). |
| Payment status | Estado del pago | `pending`, `paid`, `failed`, `refunded`. |
| Coupon | Cupón | A code giving a discount under conditions. |
| Webhook | Webhook | Signed server-to-server message from the payment provider. |
| Idempotency | Idempotencia | Processing the same event twice has the same effect as once. |
| Audit log | Registro de auditoría | Record of who did what and when. |
| MVP | MVP | Minimum Viable Product. |
