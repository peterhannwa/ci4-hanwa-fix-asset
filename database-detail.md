# Fixed Asset Management System - Database Documentation

This document provides a comprehensive overview of the database structure for the Fixed Asset Management System. The system is designed to track, manage, and report on an organization's fixed assets throughout their lifecycle, including acquisition, depreciation, maintenance, transfers, and disposal.

## Database Schema Overview

The database consists of 8 interconnected tables that track various aspects of fixed asset management:

1. **asset_categories** - Classifies assets into different categories
2. **suppliers** - Stores information about asset vendors/suppliers
3. **depreciation_methods** - Defines various methods for calculating asset depreciation
4. **assets** - Core table storing all fixed asset information
5. **depreciation_schedules** - Records depreciation calculations over time
6. **maintenance_history** - Tracks maintenance activities for assets
7. **asset_transfers** - Records movement of assets between locations
8. **asset_disposals** - Documents disposal of assets
9. **asset_conditions** - Tracks the condition of assets over time

## Entity Relationship Diagram

```
+-------------------+       +---------------+       +---------------------+
| asset_categories  |       | suppliers     |       | depreciation_methods|
+-------------------+       +---------------+       +---------------------+
| PK: category_id   |       | PK: supplier_id|      | PK: method_id      |
+-------------------+       +---------------+       +---------------------+
          |                        |                         |
          |                        |                         |
          v                        v                         v
+------------------------------------------------------------------+
|                           assets                                 |
+------------------------------------------------------------------+
| PK: asset_id                                                     |
| FK: category_id, supplier_id, depreciation_method_id             |
+------------------------------------------------------------------+
          |                |                |                 |
          |                |                |                 |
          v                v                v                 v
+------------------+ +----------------+ +--------------+ +-------------+
|depreciation_     | |maintenance_    | |asset_        | |asset_       |
|schedules         | |history         | |transfers     | |disposals    |
+------------------+ +----------------+ +--------------+ +-------------+
| PK: schedule_id  | | PK: maintenance_| | PK: transfer_| | PK: disposal_|
| FK: asset_id     | | FK: asset_id    | | FK: asset_id | | FK: asset_id |
+------------------+ +----------------+ +--------------+ +-------------+
                                                                |
                                                                v
                                                      +------------------+
                                                      | asset_conditions |
                                                      +------------------+
                                                      | PK: condition_id |
                                                      | FK: asset_id     |
                                                      +------------------+
```

## Detailed Table Descriptions

### 1. asset_categories

Stores classifications for assets (e.g., IT equipment, furniture, vehicles).

| Column | Type | Description |
|--------|------|-------------|
| category_id | INT | Primary key, auto-increment |
| category_name | VARCHAR(100) | Unique category name |
| description | TEXT | Optional description of the category |

**Relationships:**
- One-to-many relationship with the `assets` table (one category can have many assets)

### 2. suppliers

Stores information about asset suppliers or vendors.

| Column | Type | Description |
|--------|------|-------------|
| supplier_id | INT | Primary key, auto-increment |
| supplier_name | VARCHAR(255) | Name of the supplier |
| contact_person | VARCHAR(100) | Optional contact person name |
| phone_number | VARCHAR(20) | Optional contact phone number |
| email | VARCHAR(255) | Optional contact email |
| address | TEXT | Optional supplier address |

**Relationships:**
- One-to-many relationship with the `assets` table (one supplier can provide many assets)

### 3. depreciation_methods

Defines different methods for calculating asset depreciation.

| Column | Type | Description |
|--------|------|-------------|
| method_id | INT | Primary key, auto-increment |
| method_name | VARCHAR(100) | Unique method name |
| description | TEXT | Optional description of the method |

**Default Values:**
- Straight-Line
- Declining Balance
- Sum-of-the-Years' Digits
- Units of Production

**Relationships:**
- One-to-many relationship with the `assets` table (one method can be used for many assets)

### 4. assets

Core table for tracking fixed assets throughout their lifecycle.

| Column | Type | Description |
|--------|------|-------------|
| asset_id | INT | Primary key, auto-increment |
| unique_tag | VARCHAR(50) | Unique asset identifier (for barcode/QR) |
| asset_name | VARCHAR(255) | Name of the asset |
| description | TEXT | Optional detailed description |
| category_id | INT | Foreign key to asset_categories |
| acquisition_date | DATE | Date the asset was acquired |
| acquisition_cost | DECIMAL(15,2) | Original purchase cost |
| supplier_id | INT | Foreign key to suppliers (nullable) |
| warranty_expiration_date | DATE | Optional warranty expiration date |
| current_value | DECIMAL(15,2) | Current book value after depreciation |
| useful_life_years | INT | Expected useful life in years |
| salvage_value | DECIMAL(15,2) | Expected value at end of life |
| depreciation_method_id | INT | Foreign key to depreciation_methods |
| location | VARCHAR(255) | Current physical location |
| status | ENUM | 'Active', 'In Maintenance', 'Disposed', 'Transferred' |
| serial_number | VARCHAR(100) | Optional manufacturer serial number |
| manufacturer | VARCHAR(255) | Optional manufacturer name |
| purchase_order_number | VARCHAR(50) | Optional reference to purchase order |

**Relationships:**
- Many-to-one with `asset_categories` (many assets can belong to one category)
- Many-to-one with `suppliers` (many assets can come from one supplier)
- Many-to-one with `depreciation_methods` (many assets can use one depreciation method)
- One-to-many with `depreciation_schedules` (one asset can have many depreciation entries)
- One-to-many with `maintenance_history` (one asset can have many maintenance records)
- One-to-many with `asset_transfers` (one asset can have many transfer records)
- One-to-many with `asset_disposals` (one asset can have many disposal records, though typically just one)
- One-to-many with `asset_conditions` (one asset can have many condition assessments)

### 5. depreciation_schedules

Tracks calculated depreciation for each asset over time.

| Column | Type | Description |
|--------|------|-------------|
| schedule_id | INT | Primary key, auto-increment |
| asset_id | INT | Foreign key to assets |
| depreciation_date | DATE | Date of the depreciation calculation |
| depreciation_amount | DECIMAL(15,2) | Amount depreciated for this period |
| accumulated_depreciation | DECIMAL(15,2) | Total depreciation to date |
| book_value_after_depreciation | DECIMAL(15,2) | Remaining value after depreciation |

**Relationships:**
- Many-to-one with `assets` (many depreciation records can belong to one asset)

### 6. maintenance_history

Records maintenance activities performed on assets.

| Column | Type | Description |
|--------|------|-------------|
| maintenance_id | INT | Primary key, auto-increment |
| asset_id | INT | Foreign key to assets |
| maintenance_date | DATE | Date maintenance was performed |
| description | TEXT | Description of maintenance activity |
| cost | DECIMAL(15,2) | Optional cost of maintenance |
| performed_by | VARCHAR(255) | Optional person/company who performed maintenance |
| next_due_date | DATE | Optional date when next maintenance is due |

**Relationships:**
- Many-to-one with `assets` (many maintenance records can belong to one asset)

### 7. asset_transfers

Tracks movement of assets between locations or departments.

| Column | Type | Description |
|--------|------|-------------|
| transfer_id | INT | Primary key, auto-increment |
| asset_id | INT | Foreign key to assets |
| transfer_date | DATE | Date of the transfer |
| from_location | VARCHAR(255) | Original location |
| to_location | VARCHAR(255) | New location |
| transferred_by | VARCHAR(255) | Optional person who performed the transfer |
| reason | TEXT | Optional reason for the transfer |

**Relationships:**
- Many-to-one with `assets` (many transfer records can belong to one asset)

### 8. asset_disposals

Records disposal events for assets that are no longer in use.

| Column | Type | Description |
|--------|------|-------------|
| disposal_id | INT | Primary key, auto-increment |
| asset_id | INT | Foreign key to assets |
| disposal_date | DATE | Date of the disposal |
| disposal_method | VARCHAR(100) | Method (Sold, Scrapped, Donated, etc.) |
| sale_price | DECIMAL(15,2) | Optional amount if asset was sold |
| reason | TEXT | Optional reason for disposal |
| disposed_by | VARCHAR(255) | Optional person who handled the disposal |

**Relationships:**
- Many-to-one with `assets` (many disposal records can belong to one asset, though typically just one)

### 9. asset_conditions

Tracks the condition of assets over time through periodic assessments.

| Column | Type | Description |
|--------|------|-------------|
| condition_id | INT | Primary key, auto-increment |
| asset_id | INT | Foreign key to assets |
| assessment_date | DATE | Date of the condition assessment |
| condition_rating | ENUM | 'Excellent', 'Good', 'Fair', 'Poor', 'End-of-Life' |
| notes | TEXT | Optional additional notes |
| assessed_by | VARCHAR(255) | Optional person who performed the assessment |

**Relationships:**
- Many-to-one with `assets` (many condition records can belong to one asset)

## Key Constraints and Integrity Rules

1. **Foreign Key Constraints:**
   - All child tables have appropriate foreign key constraints to the parent tables
   - Most relationships use CASCADE for updates to propagate changes
   - For deletion:
     - Category and depreciation method relationships use RESTRICT to prevent deletion if assets exist
     - Supplier relationship uses SET NULL to allow supplier deletion while preserving asset records
     - All child tables of assets use CASCADE to ensure when an asset is deleted, all related records are removed

2. **Unique Constraints:**
   - `unique_tag` in the assets table ensures no duplicate asset tags
   - `category_name` in asset_categories ensures unique categories
   - `method_name` in depreciation_methods ensures unique method names

3. **Not Null Constraints:**
   - Critical fields like asset_name, acquisition_date, and cost cannot be null
   - Foreign keys to categories and depreciation methods cannot be null
   - Supplier can be null to accommodate cases where supplier information is unknown

## Application Considerations

When building your back-end application to connect with this database:

1. **Asset Creation Flow:**
   - Ensure categories, suppliers, and depreciation methods exist before creating assets
   - Calculate and update depreciation schedules when creating new assets

2. **Depreciation Processing:**
   - Implement business logic to calculate depreciation based on the selected method
   - Update the current_value in the assets table when new depreciation entries are added
   - Consider scheduling periodic depreciation calculations (e.g., monthly or annually)

3. **Asset Lifecycle Management:**
   - Update asset status when recording transfers, maintenance, or disposals
   - Implement validation to prevent operations on already disposed assets

4. **Reporting Capabilities:**
   - Design queries to support common fixed asset reports:
     - Asset register
     - Depreciation schedules
     - Maintenance history
     - Disposal reports
     - Current valuation reports

5. **Transaction Management:**
   - Use transactions for operations that update multiple tables to maintain data integrity

## API Endpoint Suggestions

Consider implementing these API endpoints for your back-end:

1. **Asset Management:**
   - GET /assets - List all assets with filtering options
   - GET /assets/{id} - Get details for a specific asset
   - POST /assets - Create a new asset
   - PUT /assets/{id} - Update an existing asset
   - DELETE /assets/{id} - Delete an asset (with cascade)

2. **Categories, Suppliers, Depreciation Methods:**
   - CRUD endpoints for each reference table

3. **Asset Operations:**
   - POST /assets/{id}/depreciate - Calculate and record depreciation
   - POST /assets/{id}/transfer - Record a transfer
   - POST /assets/{id}/maintenance - Record maintenance
   - POST /assets/{id}/dispose - Record disposal
   - POST /assets/{id}/condition - Record condition assessment

4. **Reporting:**
   - GET /reports/asset-register - Generate asset register
   - GET /reports/depreciation - Generate depreciation report
   - GET /reports/valuation - Generate current valuation report

## Database Optimization Notes

1. **Indexes:**
   - The schema includes indexes on foreign keys to optimize join operations
   - Consider adding additional indexes based on common query patterns

2. **Partitioning:**
   - For very large installations, consider partitioning the depreciation_schedules table by year

3. **Archiving:**
   - Implement an archiving strategy for disposed assets after a certain period

This documentation provides a comprehensive overview of the database structure for your Fixed Asset Management System. Use it as a guide when implementing your back-end application to ensure proper data management and integrity.