# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

# Fixed Asset Management System

A comprehensive API for managing fixed assets, including acquisition, depreciation, maintenance, transfers, conditions, and disposals.

## API Endpoints

### Asset Categories

- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get a specific category
- `POST /api/categories` - Create a new category
  ```json
  {
    "category_name": "IT Equipment",
    "description": "Computer and related hardware"
  }
  ```
- `PUT /api/categories/{id}` - Update a category
- `DELETE /api/categories/{id}` - Delete a category

### Suppliers

- `GET /api/suppliers` - List all suppliers
- `GET /api/suppliers/{id}` - Get a specific supplier
- `POST /api/suppliers` - Create a new supplier
  ```json
  {
    "supplier_name": "Dell Inc.",
    "contact_person": "John Doe",
    "phone_number": "123-456-7890",
    "email": "contact@dell.com",
    "address": "123 Tech Lane, Austin, TX"
  }
  ```
- `PUT /api/suppliers/{id}` - Update a supplier
- `DELETE /api/suppliers/{id}` - Delete a supplier

### Depreciation Methods

- `GET /api/depreciation-methods` - List all depreciation methods
- `GET /api/depreciation-methods/{id}` - Get a specific method
- `POST /api/depreciation-methods` - Create a new method
  ```json
  {
    "method_name": "Custom Method",
    "description": "A custom depreciation calculation method"
  }
  ```
- `PUT /api/depreciation-methods/{id}` - Update a method
- `DELETE /api/depreciation-methods/{id}` - Delete a method

### Assets

- `GET /api/assets` - List all assets (supports filtering by category_id, status, location)
  - Optional query parameters:
    - `category_id`: Filter by category
    - `status`: Filter by status
    - `location`: Filter by location
- `GET /api/assets/{id}` - Get a specific asset with related data
- `POST /api/assets` - Create a new asset
  ```json
  {
    "unique_tag": "ASSET001",
    "asset_name": "Dell XPS Laptop",
    "description": "15-inch development laptop",
    "category_id": 1,
    "acquisition_date": "2023-01-01",
    "acquisition_cost": 1500.00,
    "supplier_id": 1,
    "warranty_expiration_date": "2024-01-01",
    "useful_life_years": 5,
    "salvage_value": 300.00,
    "depreciation_method_id": 1,
    "location": "Main Office",
    "serial_number": "XPS15-123456",
    "manufacturer": "Dell",
    "purchase_order_number": "PO-2023-001"
  }
  ```
- `PUT /api/assets/{id}` - Update an asset
- `DELETE /api/assets/{id}` - Delete an asset
- `POST /api/assets/{id}/depreciate` - Calculate next depreciation period

### Depreciation Schedules

- `GET /api/depreciation-schedules` - List all depreciation schedules
- `GET /api/depreciation-schedules/asset/{asset_id}` - Get schedules for specific asset
- `GET /api/depreciation-schedules/{id}` - Get a specific schedule entry
- `POST /api/depreciation-schedules` - Create a schedule entry
- `PUT /api/depreciation-schedules/{id}` - Update a schedule entry
- `DELETE /api/depreciation-schedules/{id}` - Delete a schedule entry

### Maintenance History

- `GET /api/maintenance` - List all maintenance records
- `GET /api/maintenance/asset/{asset_id}` - Get maintenance history for an asset
- `GET /api/maintenance/{id}` - Get a specific maintenance record
- `POST /api/maintenance` - Create a maintenance record
  ```json
  {
    "asset_id": 1,
    "maintenance_date": "2023-06-15",
    "description": "Regular cleaning and hardware check",
    "cost": 75.00,
    "performed_by": "IT Support",
    "next_due_date": "2023-12-15",
    "update_status": true
  }
  ```
- `PUT /api/maintenance/{id}` - Update a maintenance record
- `DELETE /api/maintenance/{id}` - Delete a maintenance record
- `PUT /api/maintenance/{id}/complete` - Complete maintenance and restore asset status

### Asset Transfers

- `GET /api/transfers` - List all transfer records
- `GET /api/transfers/asset/{asset_id}` - Get transfer history for an asset
- `GET /api/transfers/{id}` - Get a specific transfer record
- `POST /api/transfers` - Record an asset transfer
  ```json
  {
    "asset_id": 1,
    "transfer_date": "2023-07-10",
    "from_location": "Main Office",
    "to_location": "Branch Office",
    "transferred_by": "Jane Smith",
    "reason": "Department relocation"
  }
  ```
- `PUT /api/transfers/{id}` - Update a transfer record
- `DELETE /api/transfers/{id}` - Delete a transfer record
- `PUT /api/transfers/{id}/complete` - Complete transfer process

### Asset Disposals

- `GET /api/disposals` - List all disposal records
- `GET /api/disposals/asset/{asset_id}` - Get disposal history for an asset
- `GET /api/disposals/{id}` - Get a specific disposal record
- `POST /api/disposals` - Record an asset disposal
  ```json
  {
    "asset_id": 1,
    "disposal_date": "2023-12-01",
    "disposal_method": "Sold",
    "sale_price": 500.00,
    "reason": "Obsolete technology",
    "disposed_by": "John Manager"
  }
  ```
- `PUT /api/disposals/{id}` - Update a disposal record
- `DELETE /api/disposals/{id}` - Delete a disposal record

### Asset Conditions

- `GET /api/conditions` - List all condition assessments
- `GET /api/conditions/asset/{asset_id}` - Get condition history for an asset
- `GET /api/conditions/{id}` - Get a specific condition record
- `POST /api/conditions` - Record an asset condition assessment
  ```json
  {
    "asset_id": 1,
    "assessment_date": "2023-05-20",
    "condition_rating": "Good",
    "notes": "Minor wear and tear but functioning well",
    "assessed_by": "Maintenance Team"
  }
  ```
- `PUT /api/conditions/{id}` - Update a condition record
- `DELETE /api/conditions/{id}` - Delete a condition record

### Reports

- `GET /api/reports/asset-register` - Generate asset register report
- `GET /api/reports/depreciation` - Generate depreciation report
- `GET /api/reports/valuation` - Generate current valuation report
- `GET /api/reports/maintenance-costs` - Generate maintenance costs report

## Example API Usage

```javascript
// Get all assets
const response = await fetch('http://localhost:2311/api/assets');
const assets = await response.json();

// Create a new asset
const newAsset = {
    unique_tag: "LAPTOP001",
    asset_name: "Dell XPS 15",
    category_id: 1,
    acquisition_date: "2023-01-01",
    acquisition_cost: 1500.00,
    useful_life_years: 5,
    depreciation_method_id: 1
};

const createResponse = await fetch('http://localhost:2311/api/assets', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(newAsset)
});
```

## Running the Application

1. Start the server:
```bash
php spark serve --port 2311
```

2. Access the API at `http://localhost:2311/api/...`

The system is now complete with all controllers, models, and routes properly configured according to the database schema.

## Error Handling

The API returns appropriate HTTP status codes:

- 200 - Success
- 201 - Created
- 400 - Bad Request (validation errors)
- 404 - Not Found
- 500 - Server Error

Response format for errors:
```json
{
  "status": 400,
  "error": "Bad Request",
  "messages": {
    "field_name": "Error message"
  }
}
