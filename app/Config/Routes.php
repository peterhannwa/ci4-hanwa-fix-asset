<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();

$routes->get('/', 'Home::index');

$routes->group('api', function($routes) {
    $routes->get('assets', 'Api\AssetController::index');
    $routes->get('assets/(:num)', 'Api\AssetController::show/$1');
    $routes->post('assets', 'Api\AssetController::create');
    $routes->put('assets/(:num)', 'Api\AssetController::update/$1');
    $routes->delete('assets/(:num)', 'Api\AssetController::delete/$1');
});
