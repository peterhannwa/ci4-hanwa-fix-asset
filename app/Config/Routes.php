<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();

$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // Asset Categories
    $routes->resource('categories', ['controller' => 'CategoryController']);
    
    // Assets
    $routes->resource('assets', ['controller' => 'AssetController']);
    
    // Depreciation
    $routes->get('depreciation/calculate/(:num)', 'AssetController::calculateDepreciation/$1');
    
    // Maintenance
    $routes->get('maintenance/(:num)', 'MaintenanceController::getByAsset/$1');
    $routes->post('maintenance', 'MaintenanceController::create');
    
    // Transfers
    $routes->get('transfers/(:num)', 'TransferController::getByAsset/$1');
    $routes->post('transfers', 'TransferController::create');
    
    // Disposals
    $routes->get('disposals/(:num)', 'DisposalController::getByAsset/$1');
    $routes->post('disposals', 'DisposalController::create');
    
    // Conditions
    $routes->get('conditions/(:num)', 'ConditionController::getByAsset/$1');
    $routes->post('conditions', 'ConditionController::create');
});
