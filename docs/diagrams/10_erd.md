# 10 — Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ ORDERS : places
    USERS ||--o{ STOCK_MOVEMENTS : records
    USERS ||--o{ AUDIT_LOGS : performs
    CATEGORIES ||--o{ PRODUCTS : groups
    CATEGORIES |o--o{ COUPONS : "limits (optional)"
    PRODUCTS ||--o{ STOCK_LEVELS : has
    LOCATIONS ||--o{ STOCK_LEVELS : holds
    PRODUCTS ||--o{ BATCHES : has
    LOCATIONS ||--o{ BATCHES : stores
    PRODUCTS ||--o{ STOCK_MOVEMENTS : moves
    LOCATIONS ||--o{ STOCK_MOVEMENTS : at
    ORDERS |o--o{ STOCK_MOVEMENTS : causes
    ORDERS ||--|{ ORDER_ITEMS : contains
    PRODUCTS |o--o{ ORDER_ITEMS : "snapshot of"
    ORDERS ||--o{ ORDER_STATUS_HISTORIES : tracks
    ORDERS ||--o{ PAYMENTS : "paid by"
    COUPONS |o--o{ ORDERS : "applied to"
    ORDERS ||--o{ NOTIFICATION_LOGS : notifies

    USERS {
        bigint id PK
        string name
        string email UK
        string password "bcrypt hash"
        string role "admin|warehouse|cashier|customer"
        string phone
        bool is_active
    }
    CATEGORIES {
        bigint id PK
        string name
        string slug UK
        bool is_active
    }
    PRODUCTS {
        bigint id PK
        bigint category_id FK
        string name
        string slug UK
        text description
        string image_path
        decimal price "10,2 EUR"
        string sale_unit "unit|kg|pack"
        string shelf_location
        bool tracks_expiry
        decimal low_stock_threshold
        bool is_active
    }
    LOCATIONS {
        bigint id PK
        string name
        string type "warehouse|store"
        string address
        bool is_online_source
    }
    STOCK_LEVELS {
        bigint id PK
        bigint product_id FK
        bigint location_id FK
        decimal quantity "10,3 >= 0"
        decimal reserved "10,3 >= 0"
    }
    BATCHES {
        bigint id PK
        bigint product_id FK
        bigint location_id FK
        string lot_code
        date expiry_date
        decimal quantity
    }
    STOCK_MOVEMENTS {
        bigint id PK
        bigint product_id FK
        bigint location_id FK
        bigint user_id FK "nullable"
        bigint order_id FK "nullable"
        string type
        decimal quantity "signed"
        string reason
        datetime created_at
    }
    ORDERS {
        bigint id PK
        string number UK
        bigint user_id FK
        string status
        string payment_status
        string fulfilment_method "delivery|pickup"
        decimal subtotal
        decimal discount
        decimal delivery_fee
        decimal total
        bigint coupon_id FK "nullable"
        string coupon_code
        string customer_name
        string customer_email
        string phone
        string address
        string postcode
        string city
        text notes
        datetime paid_at
        datetime cancelled_at
    }
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK "nullable"
        string product_name "snapshot"
        string sale_unit "snapshot"
        decimal unit_price "snapshot"
        decimal quantity
        decimal line_total
    }
    ORDER_STATUS_HISTORIES {
        bigint id PK
        bigint order_id FK
        string from_status
        string to_status
        bigint user_id FK "nullable = system"
        string note
    }
    PAYMENTS {
        bigint id PK
        bigint order_id FK
        string provider "mock|stripe"
        string provider_reference
        string payment_intent
        string event_id UK
        decimal amount
        string status "pending|paid|failed|refunded"
    }
    COUPONS {
        bigint id PK
        string code UK
        string description
        string type "percent|fixed"
        decimal value
        datetime starts_at
        datetime ends_at
        decimal min_order_total
        bigint category_id FK "nullable"
        int max_uses
        int used_count
        bool is_active
    }
    NOTIFICATION_LOGS {
        bigint id PK
        bigint order_id FK
        string event
        string channel
        string recipient
        string status "sent|failed"
        text error
    }
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string subject_type
        bigint subject_id
        json details
        string ip_address
        datetime created_at
    }
```

Unique constraints: `stock_levels(product_id, location_id)`, `notification_logs(order_id, event, channel)`,
`payments.event_id`, `orders.number`, `coupons.code`, `products.slug`, `categories.slug`, `users.email`.
