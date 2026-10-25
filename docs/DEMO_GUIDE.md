# LocalMarket — Guía de Demostración y Credenciales de Acceso

**Plataforma:** LocalMarket Barcelona  
**Stack:** PHP 8.2+ / Laravel 12 / MariaDB (XAMPP) / Vanilla CSS & JS  
**Servidor local:** `http://127.0.0.1:8000` (o `http://localhost:8000`)  

---

## 1. Credenciales de Demostración

La base de datos se encuentra preconfigurada mediante `php artisan db:seed` con usuarios para cada uno de los cuatro roles del sistema:

| Rol | Correo Electrónico | Contraseña | Acceso / Secciones |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@localmarket.test` | `password123` | **Panel Completo:** Catálogo (Productos y Categorías), Cupones, Gestión de Usuarios, Auditoría, Inventario, TPV y Pedidos. |
| **Mozo de Almacén** | `almacen@localmarket.test` | `password123` | **Inventario y Operaciones:** Recepción de mercancía (con control de lote y caducidad), traspasos Almacén &rarr; Tienda, ajustes de inventario y consulta de lotes. |
| **Cajero de Tienda** | `cajero@localmarket.test` | `password123` | **TPV en Tienda:** Venta presencial rápida que descuenta automáticamente el stock físico de la tienda. |
| **Cliente** | `cliente@localmarket.test` | `password123` | **Tienda Online:** Navegación por catálogo, carrito, cupones, checkout (domicilio Barcelona 08001–08042 o recogida), pasarela de pago simulada y seguimiento de pedidos. |

---

## 2. Cupones de Demostración Activos

| Código Cupón | Tipo | Descuento | Pedido Mínimo | Estado |
| :--- | :--- | :--- | :--- | :--- |
| `BIENVENIDA10` | Porcentaje | **10%** | 15,00 € | Activo |
| `FRESCOS5` | Importe Fijo | **5,00 €** | 30,00 € | Activo |
| `VERANO20` | Porcentaje | **20%** | 40,00 € | Activo |

---

## 3. Recorridos de Demostración Recomendados (E2E)

### Recorrido A: Compra Online por Cliente
1. Acceder a `http://127.0.0.1:8000` y navegar al **Catálogo**.
2. Seleccionar productos frescos (ej. *Plátano de Canarias* por peso en kg, o *Leche Entera* por unidades) y pulsar **Añadir a la cesta**.
3. Ir a la **Cesta** (`/cesta`), revisar cantidades e iniciar el **Checkout** (`/checkout`).
4. Iniciar sesión como `cliente@localmarket.test` / `password123`.
5. En el checkout:
   - Aplicar el cupón `BIENVENIDA10` para verificar la deducción del 10%.
   - Seleccionar entrega a domicilio e introducir un código postal válido de Barcelona (ej. `08001`, `08036`).
   - Confirmar el pedido para verificar la **reserva inmediata de stock**.
6. En la **Pasarela de Pago** (`/pedido/{id}/pago`):
   - Probar el conmutador de simulación (Simular Pago Exitoso / Simular Fallo).
   - Al pulsar pagar, el pedido pasa a estado **Confirmado**, el stock reservado se descuenta del inventario físico y se genera el correo de confirmación.

### Recorrido B: Operaciones de Almacén (Mozo)
1. Iniciar sesión como `almacen@localmarket.test` / `password123`.
2. Acceder al módulo de **Inventario** (`/gestion/inventario`).
3. Registrar una **Entrada de mercancía** en el *Almacén Central* asignando código de lote (ej. `LOT-2026-X`) y fecha de caducidad.
4. Realizar un **Traspaso** de mercancía desde el Almacén hacia la *Tienda Central*.
5. Comprobar cómo el libro mayor inmutable registra el movimiento en `/gestion/inventario/movimientos`.

### Recorrido C: Venta Rápida en Caja (Cajero)
1. Iniciar sesión como `cajero@localmarket.test` / `password123`.
2. Acceder al **TPV** (`/gestion/tpv`).
3. Seleccionar el producto vendido y la cantidad.
4. Confirmar la venta presencial: el stock de la tienda se descuenta en tiempo real.

### Recorrido D: Panel de Administración y Auditoría (Admin)
1. Iniciar sesión como `admin@localmarket.test` / `password123`.
2. Explorar el **Resumen del Dashboard** con avisos de stock bajo y caducidades próximas (&le; 3 días).
3. Gestionar **Cupones** (`/gestion/cupones`) y **Usuarios** (`/gestion/usuarios`), incluyendo la activación/desactivación de cuentas.
4. Consultar las **Pistas de Auditoría** (`/gestion/auditoria`) para ver el registro detallado con usuario, acción, IP y payload de todas las operaciones realizadas.

---

## 4. Comandos de Mantenimiento

```bash
# Ejecutar todas las migraciones y sembrar datos de prueba
php artisan migrate --seed

# Ejecutar el comando de expiración de pedidos impagados (libera reserva)
php artisan orders:expire-unpaid --minutes=30

# Ejecutar la suite completa de 59 pruebas automatizadas
php artisan test

# Comprobar estilo de código conforme al estándar PSR-12 / Laravel
vendor/bin/pint --test
```
