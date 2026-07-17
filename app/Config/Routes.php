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
    $routes->get('items/archived', 'Items::archived');
    $routes->post('items/restore/(:num)', 'Items::restore/$1');
    $routes->get('items/download-template', 'Items::downloadTemplate');
    $routes->get('items/bulk-upload', 'Items::bulkUploadForm');
    $routes->post('items/bulk-upload/process', 'Items::bulkUpload');
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
    $routes->get('units/edit/(:num)', 'Units::edit/$1');
    $routes->post('units/edit/(:num)', 'Units::edit/$1');
    $routes->post('units/delete/(:num)', 'Units::delete/$1');
    $routes->post('units/quick-add', 'Units::quickAdd');

    // Brands
    $routes->get('brands', 'Brands::index');
    $routes->get('brands/add', 'Brands::add');
    $routes->post('brands/add', 'Brands::add');
    $routes->post('brands/quick-add', 'Brands::quickAdd');

    // Stock Manager / Stock Alert
    $routes->get('stock/manager', 'StockManager::index');
    $routes->post('stock/adjust', 'StockManager::adjust');
    $routes->post('stock/update-price/(:num)', 'StockManager::updatePrice/$1');
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
    $routes->get('purchase/lpo/(:num)', 'Purchase::lpo/$1');
    $routes->get('purchase/lpo-no-price/(:num)', 'Purchase::lpoNoPrice/$1');
    $routes->get('purchase/thermal/(:num)', 'Purchase::thermalPrint/$1');

    // Purchase Payments
    $routes->get('purchase/payments/(:num)', 'Purchase::viewPayments/$1');
    $routes->post('purchase/payments/(:num)/add', 'Purchase::addPayment/$1');
    $routes->post('purchase/payments/delete/(:num)', 'Purchase::deletePayment/$1');

    // Debit Notes
    $routes->get('purchase/debit-notes', 'DebitNotes::index');

    // POS
    $routes->get('pos', 'Pos::index');
    $routes->post('pos/checkout', 'Pos::checkout');
    $routes->post('pos/hold', 'Pos::hold');
    $routes->get('pos/held-list', 'Pos::heldList');
    $routes->get('pos/recall/(:num)', 'Pos::recall/$1');

    // Sales
    $routes->get('sales/list', 'Sales::index');
    $routes->get('sales/view/(:num)', 'Sales::view/$1');

    // Customers
    $routes->get('customers', 'Customers::index');
    $routes->get('customers/add', 'Customers::add');
    $routes->post('customers/add', 'Customers::add');
    $routes->post('customers/quick-add', 'Customers::quickAdd');

    // Accounting
    $routes->get('accounting/account-types', 'AccountTypes::index');
    $routes->get('accounting/account-types/add', 'AccountTypes::add');
    $routes->post('accounting/account-types/add', 'AccountTypes::add');

    $routes->get('accounting/sub-account-types', 'SubAccountTypes::index');
    $routes->get('accounting/sub-account-types/add', 'SubAccountTypes::add');
    $routes->post('accounting/sub-account-types/add', 'SubAccountTypes::add');

    $routes->get('accounting/chart-of-accounts', 'ChartOfAccounts::index');
    $routes->get('accounting/chart-of-accounts/add', 'ChartOfAccounts::add');
    $routes->post('accounting/chart-of-accounts/add', 'ChartOfAccounts::add');

    $routes->get('accounting/money', 'Money::index');
    $routes->get('accounting/money/make-payment', 'Money::makePayment');
    $routes->post('accounting/money/make-payment', 'Money::makePayment');
    $routes->get('accounting/money/receive-payment', 'Money::receivePayment');
    $routes->post('accounting/money/receive-payment', 'Money::receivePayment');

    // Issued / Damaged Products, Price Change Log
    $routes->get('issued-products', 'IssuedProducts::index');
    $routes->get('issued-products/add', 'IssuedProducts::addForm');
    $routes->post('issued-products/add', 'IssuedProducts::add');
    $routes->post('issued-products/delete/(:num)', 'IssuedProducts::delete/$1');

    $routes->get('damaged-products', 'DamagedProducts::index');
    $routes->get('damaged-products/add', 'DamagedProducts::addForm');
    $routes->post('damaged-products/add', 'DamagedProducts::add');
    $routes->post('damaged-products/delete/(:num)', 'DamagedProducts::delete/$1');

    // Stock Conversion
    $routes->get('stock-conversion', 'StockConversion::index');
    $routes->get('stock-conversion/add', 'StockConversion::addForm');
    $routes->post('stock-conversion/add', 'StockConversion::add');
    $routes->get('price-change-log', 'PriceChangeLog::index');
});
