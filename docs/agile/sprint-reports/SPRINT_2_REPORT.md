# Sprint 2 Report — Product Catalogue

- **Sprint Goal:** Deliver product categories and products data models, public customer catalogue with search and category filtering, product detail view, and administrator product & category CRUD with audit trail.
- **Dates (Provisional):** Relative Week 3 – Week 4
- **Branch:** `sprint/2-product-catalog`
- **Merge Target:** `develop`

## 1. Planned vs. Completed User Stories

| Story ID | Description | Owner | Status | Evidence |
|---|---|---|---|---|
| **US-04** | Customer browses and searches catalogue by category and keywords | Developer B | Completed | `CatalogTest` (filtering, search, pagination, inactive check) |
| **US-05** | Customer views product details, prices in EUR, units, and availability | Developer B | Completed | `CatalogTest` (product details view, 404 for inactive products) |
| **US-06** | Administrator manages products and categories with audit trail | Developer A | Completed | `ProductAdminTest` (create category/product, validation, update, audit logging) |

## 2. Implementation Changes

- **Database Migrations:**
  - `2026_10_25_000300_create_categories_table.php`: Category name, slug, description, icon (emoji), and active status.
  - `2026_10_25_000400_create_products_table.php`: Product name, slug, price (decimal 10,2), sale unit (`unit`, `kg`, `pack`), shelf location, perishable batch tracking flag (`tracks_expiry`), low-stock threshold, and active flag.
- **Models & Factories:**
  - `app/Models/Category.php` and `database/factories/CategoryFactory.php`.
  - `app/Models/Product.php` and `database/factories/ProductFactory.php` (with `UNITS` definition and unit helpers).
- **Controllers & Routing:**
  - `app/Http/Controllers/HomeController.php`: Homepage displaying featured products and category chips.
  - `app/Http/Controllers/CatalogController.php`: Public catalogue with filtering by category and search by keyword.
  - `app/Http/Controllers/Staff/CategoryController.php`: Admin CRUD for categories with AuditLog integration.
  - `app/Http/Controllers/Staff/ProductController.php`: Admin CRUD for products with AuditLog integration.
- **Views:**
  - `resources/views/welcome.blade.php`: Modern supermarket homepage.
  - `resources/views/catalog/index.blade.php`: Customer catalogue list.
  - `resources/views/catalog/show.blade.php`: Product detail card with unit breakdown and related products.
  - `resources/views/staff/categories/`: Category list, create, and edit forms.
  - `resources/views/staff/products/`: Product list with filters, create, and edit forms.
- **Seeders:**
  - `database/seeders/DatabaseSeeder.php`: Updated with 6 realistic grocery categories and 9 representative products (fruits, meat, dairy, bakery, pantry).

## 3. Test Results

Executed with `php artisan test`:
- `Tests\Unit\ExampleTest`: 1 passed
- `Tests\Feature\AuthTest`: 8 passed
- `Tests\Feature\CatalogTest`: 6 passed
- `Tests\Feature\PermissionTest`: 5 passed
- `Tests\Feature\ProductAdminTest`: 5 passed
- `Tests\Feature\ExampleTest`: 1 passed
- **Total:** 26 passed, 78 assertions.
- Code style: Checked with `vendor/bin/pint --test` (Passed).

## 4. Known Issues & Next Sprint Carry-over

- No blockers or unresolved issues.
- Carry-over to Sprint 3: Inventory management (Locations, Stock Levels, Stock Movements, Receipts, Transfers, Adjustments, Batches & FEFO expiry alerts).
