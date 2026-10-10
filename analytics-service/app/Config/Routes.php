<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/about', 'About::index');
$routes->get('/blog', 'Blog::index');
$routes->get('/foodmart','FoodmartController::index');
$routes->get('/foodmartAPI/(:any)/(:any)', 'FoodmartAPI::index/$1/$2');
$routes->get('/login', 'LoginController::index');
$routes->get('/logout', 'LoginController::logout');
$routes->get('/news', 'NewsController::index');
$routes->get('/report', 'ReportController::index');
$routes->post('/login_action', 'LoginController::login_action');

$routes->group('analytics', function ($routes) {
    $routes->get('top-products',  'AnalyticsController::topProducts');
    $routes->get('top-customers', 'AnalyticsController::topCustomers');
    $routes->get('sales-trend',   'AnalyticsController::salesTrend');
    $routes->get('health',        'AnalyticsController::health');
});

