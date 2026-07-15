<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Default: send root straight to login (Auth::login also redirects to dashboard if already logged in)
$routes->get('/', 'Auth::login');

// ---------------- Public routes (no auth filter) ----------------
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// ---------------- Authenticated routes ----------------
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // Items/Products
    $routes->get('items/list', 'Items::index');
    $routes->get('items/add', 'Items::add');
    $routes->post('items/add', 'Items::add');
    $routes->get('items/edit/(:num)', 'Items::edit/$1');
    $routes->post('items/edit/(:num)', 'Items::edit/$1');
    $routes->get('items/profile/(:num)', 'Items::profile/$1');
    $routes->post('items/conversion-create/(:num)', 'Items::conversionCreate/$1');
    $routes->post('items/delete/(:num)', 'Items::delete/$1');

    // Categories
    $routes->get('category/view', 'Categories::index');
    $routes->get('category/add', 'Categories::add');
    $routes->post('category/add', 'Categories::add');
    $routes->get('category/edit/(:num)', 'Categories::edit/$1');
    $routes->post('category/edit/(:num)', 'Categories::edit/$1');
    $routes->post('category/quick-add', 'Categories::quickAdd');

    // Units
    $routes->get('units', 'Units::index');
    $routes->get('units/add', 'Units::add');
    $routes->post('units/add', 'Units::add');
    $routes->post('units/quick-add', 'Units::quickAdd');

    // Brands
    $routes->get('brands', 'Brands::index');
    $routes->get('brands/add', 'Brands::add');
    $routes->post('brands/add', 'Brands::add');
    $routes->post('brands/quick-add', 'Brands::quickAdd');

    // Stock Manager / Stock Alert
    $routes->get('stock/manager', 'StockManager::index');
    $routes->post('stock/adjust', 'StockManager::adjust');
    $routes->get('stock/alerts', 'StockManager::alerts');

    // Print Labels
    $routes->get('items/print-labels', 'PrintLabels::index');
    $routes->get('items/print-labels/sheet', 'PrintLabels::sheet');

    // Suppliers
    $routes->get('suppliers', 'Suppliers::index');
    $routes->get('suppliers/add', 'Suppliers::add');
    $routes->post('suppliers/add', 'Suppliers::add');
    $routes->post('suppliers/quick-add', 'Suppliers::quickAdd');

    // Purchase
    $routes->get('purchase/list', 'Purchase::index');
    $routes->get('purchase/add', 'Purchase::add');
    $routes->post('purchase/add', 'Purchase::add');
    $routes->get('purchase/view/(:num)', 'Purchase::view/$1');

    // Purchase Return
    $routes->get('purchase/returns', 'Purchase::returns');
    $routes->get('purchase/return/add', 'Purchase::returnAdd');
    $routes->post('purchase/return/add', 'Purchase::returnAdd');
    $routes->get('purchase/lines-json/(:num)', 'Purchase::linesJson/$1');
});
