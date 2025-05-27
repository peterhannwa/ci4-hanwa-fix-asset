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

## API Documentation

### Available Endpoints

1. Asset Management
```javascript
// Get all assets
GET /api/assets

// Get single asset with related data
GET /api/assets/{id}

// Create new asset
POST /api/assets
{
    "unique_tag": "AST001",
    "asset_name": "Dell Laptop XPS",
    "category_id": 1,
    "acquisition_date": "2023-01-01",
    "acquisition_cost": 1500.00,
    "supplier_id": 1,
    "depreciation_method_id": 1,
    "useful_life_years": 5,
    "salvage_value": 300.00
}
```

2. Maintenance Records
```javascript
// Get maintenance history
GET /api/maintenance/{asset_id}

// Record maintenance
POST /api/maintenance
{
    "asset_id": 1,
    "maintenance_date": "2023-12-01",
    "description": "Regular maintenance",
    "cost": 150.00,
    "performed_by": "John Doe"
}
```

3. Asset Transfers
```javascript
// Record transfer
POST /api/transfers
{
    "asset_id": 1,
    "transfer_date": "2023-12-01",
    "from_location": "Main Office",
    "to_location": "Branch Office",
    "transferred_by": "Jane Smith"
}
```

4. Asset Disposal
```javascript
// Record disposal
POST /api/disposals
{
    "asset_id": 1,
    "disposal_date": "2023-12-01",
    "disposal_method": "Sold",
    "sale_price": 500.00,
    "disposed_by": "John Manager"
}
```

## Frontend Integration Example

```typescript
// Asset Service
class AssetService {
    private readonly baseUrl = 'http://localhost:2311/api';

    async getAllAssets() {
        const response = await fetch(`${this.baseUrl}/assets`);
        return response.json();
    }

    async createAsset(assetData) {
        const response = await fetch(`${this.baseUrl}/assets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(assetData)
        });
        return response.json();
    }

    async recordMaintenance(maintenanceData) {
        const response = await fetch(`${this.baseUrl}/maintenance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(maintenanceData)
        });
        return response.json();
    }

    private getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        };
    }
}
```

## Error Handling

The API returns standard HTTP status codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 404: Not Found
- 500: Server Error

## Running the Application

1. Start the backend server:
```bash
cd d:\learn\codeigniter\ci4-hanwa-fix-asset
php spark serve --port 2311
```

2. Configure your frontend application to use the API endpoint:
```javascript
const API_BASE_URL = 'http://localhost:2311/api';
```
cd d:\learn\codeigniter\ci4-hanwa-fix-asset
php spark serve --port 2311
```

2. Configure your frontend application to use the API endpoint:
```javascript
const API_BASE_URL = 'http://localhost:2311/api';
```
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('API call failed:', error);
        throw error;
    }
}
```

## Testing the API

You can use tools like Postman or curl to test the API endpoints:

```bash
# Get all assets
curl -X GET http://localhost:2311/api/assets

# Create new asset
curl -X POST http://localhost:2311/api/assets \
  -H "Content-Type: application/json" \
  -d '{"unique_tag":"AST001","asset_name":"Dell Laptop",...}'
```

## Frontend Framework Recommendations

1. React.js Setup:
```bash
npx create-react-app asset-management-frontend
cd asset-management-frontend
npm install axios @material-ui/core @material-ui/icons
```

2. Vue.js Setup:
```bash
npm init vue@latest asset-management-frontend
cd asset-management-frontend
npm install axios primevue
```

3. Angular Setup:
```bash
ng new asset-management-frontend
cd asset-management-frontend
ng add @angular/material
npm install axios
```
