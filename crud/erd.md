# Entity-Relationship Diagram (ERD)

## Database Schema: Employees and Products

```mermaid
erDiagram
    EMPLOYEES {
        int id PK
        string employee_code UK
        string first_name
        string last_name
        string email UK
        string phone
        string position
        string department
        date hire_date
        decimal salary
        enum status
        timestamp created_at
        timestamp updated_at
    }
    
    PRODUCTS {
        int id PK
        string product_code UK
        string name
        text description
        string category
        decimal price
        decimal cost
        int stock_quantity
        int min_stock_level
        string unit
        enum status
        int created_by FK
        timestamp created_at
        timestamp updated_at
    }
    
    EMPLOYEE_PRODUCTS {
        int id PK
        int employee_id FK
        int product_id FK
        date assigned_date
        text notes
        timestamp created_at
    }
    
    EMPLOYEES ||--o{ PRODUCTS : "creates"
    EMPLOYEES ||--o{ EMPLOYEE_PRODUCTS : "manages"
    PRODUCTS ||--o{ EMPLOYEE_PRODUCTS : "assigned_to"
```

## Entity Descriptions

### EMPLOYEES
- **Primary Key**: `id`
- **Unique Keys**: `employee_code`, `email`
- **Key Attributes**:
  - Personal information: first_name, last_name, email, phone
  - Employment details: position, department, hire_date, salary
  - Status tracking: status (active/inactive/terminated)

### PRODUCTS
- **Primary Key**: `id`
- **Unique Keys**: `product_code`
- **Foreign Key**: `created_by` → EMPLOYEES.id
- **Key Attributes**:
  - Product information: name, description, category
  - Pricing: price, cost
  - Inventory: stock_quantity, min_stock_level, unit
  - Status tracking: status (active/discontinued/out_of_stock)

### EMPLOYEE_PRODUCTS (Junction Table)
- **Primary Key**: `id`
- **Foreign Keys**: 
  - `employee_id` → EMPLOYEES.id
  - `product_id` → PRODUCTS.id
- **Purpose**: Tracks which employees are assigned to manage specific products
- **Unique Constraint**: One employee-product assignment per combination

## Relationships

1. **EMPLOYEES → PRODUCTS (One-to-Many)**
   - One employee can create multiple products
   - Foreign key: `products.created_by`
   - Optional relationship (ON DELETE SET NULL)

2. **EMPLOYEES ↔ PRODUCTS (Many-to-Many via EMPLOYEE_PRODUCTS)**
   - Employees can manage multiple products
   - Products can be managed by multiple employees
   - Junction table: `EMPLOYEE_PRODUCTS`
   - Cascade delete on employee/product removal
