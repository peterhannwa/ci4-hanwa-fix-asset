<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // Asset Categories
    $routes->get('categories', 'AssetCategoryController::index');
    $routes->get('categories/(:num)', 'AssetCategoryController::show/$1');
    $routes->post('categories', 'AssetCategoryController::create');
    $routes->put('categories/(:num)', 'AssetCategoryController::update/$1');
    $routes->delete('categories/(:num)', 'AssetCategoryController::delete/$1');
    
    // Suppliers
    $routes->get('suppliers', 'SupplierController::index');
    $routes->get('suppliers/(:num)', 'SupplierController::show/$1');
    $routes->post('suppliers', 'SupplierController::create');
    $routes->put('suppliers/(:num)', 'SupplierController::update/$1');
    $routes->delete('suppliers/(:num)', 'SupplierController::delete/$1');
    
    // Depreciation Methods
    $routes->get('depreciation-methods', 'DepreciationMethodController::index');
    $routes->get('depreciation-methods/(:num)', 'DepreciationMethodController::show/$1');
    $routes->post('depreciation-methods', 'DepreciationMethodController::create');
    $routes->put('depreciation-methods/(:num)', 'DepreciationMethodController::update/$1');
    $routes->delete('depreciation-methods/(:num)', 'DepreciationMethodController::delete/$1');
    
    // Assets
    $routes->get('assets', 'AssetController::index');
    $routes->get('assets/(:num)', 'AssetController::show/$1');
    $routes->post('assets', 'AssetController::create');
    $routes->put('assets/(:num)', 'AssetController::update/$1');
    $routes->delete('assets/(:num)', 'AssetController::delete/$1');
    $routes->post('assets/(:num)/depreciate', 'AssetController::depreciate/$1');
    
    // Depreciation Schedules
    $routes->get('depreciation-schedules', 'DepreciationScheduleController::index');
    $routes->get('depreciation-schedules/asset/(:num)', 'DepreciationScheduleController::getByAsset/$1');
    $routes->get('depreciation-schedules/(:num)', 'DepreciationScheduleController::show/$1');
    $routes->post('depreciation-schedules', 'DepreciationScheduleController::create');
    $routes->put('depreciation-schedules/(:num)', 'DepreciationScheduleController::update/$1');
    $routes->delete('depreciation-schedules/(:num)', 'DepreciationScheduleController::delete/$1');
    
    // Maintenance
    $routes->get('maintenance', 'MaintenanceController::index');
    $routes->get('maintenance/asset/(:num)', 'MaintenanceController::getByAsset/$1');
    $routes->get('maintenance/(:num)', 'MaintenanceController::show/$1');
    $routes->post('maintenance', 'MaintenanceController::create');
    $routes->put('maintenance/(:num)', 'MaintenanceController::update/$1');
    $routes->delete('maintenance/(:num)', 'MaintenanceController::delete/$1');
    $routes->put('maintenance/(:num)/complete', 'MaintenanceController::complete/$1');
    
    // Transfers
    $routes->get('transfers', 'TransferController::index');
    $routes->get('transfers/asset/(:num)', 'TransferController::getByAsset/$1');
    $routes->get('transfers/(:num)', 'TransferController::show/$1');
    $routes->post('transfers', 'TransferController::create');
    $routes->put('transfers/(:num)', 'TransferController::update/$1');
    $routes->delete('transfers/(:num)', 'TransferController::delete/$1');
    $routes->put('transfers/(:num)/complete', 'TransferController::complete/$1');
    
    // Disposals
    $routes->get('disposals', 'DisposalController::index');
    $routes->get('disposals/asset/(:num)', 'DisposalController::getByAsset/$1');
    $routes->get('disposals/(:num)', 'DisposalController::show/$1');
    $routes->post('disposals', 'DisposalController::create');
    $routes->put('disposals/(:num)', 'DisposalController::update/$1');
    $routes->delete('disposals/(:num)', 'DisposalController::delete/$1');
    
    // Conditions
    $routes->get('conditions', 'ConditionController::index');
    $routes->get('conditions/asset/(:num)', 'ConditionController::getByAsset/$1');
    $routes->get('conditions/(:num)', 'ConditionController::show/$1');
    $routes->post('conditions', 'ConditionController::create');
    $routes->put('conditions/(:num)', 'ConditionController::update/$1');
    $routes->delete('conditions/(:num)', 'ConditionController::delete/$1');

    // Reports
    $routes->get('reports/asset-register', 'ReportController::assetRegister');
    $routes->get('reports/depreciation', 'ReportController::depreciation');
    $routes->get('reports/valuation', 'ReportController::valuation');
    $routes->get('reports/maintenance-costs', 'ReportController::maintenanceCosts');
});

// Default route
$routes->get('/', 'Home::index');
