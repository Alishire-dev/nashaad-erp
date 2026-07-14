<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? esc($title) . ' - NASHAAD ERP' : 'NASHAAD ERP' ?></title>
    <style>
        * { box-sizing:border-box; }
        body { font-family: Arial, sans-serif; margin:0; background:#f4f5f7; }
        .topbar { background:#e07b1e; color:#fff; display:flex; align-items:center; justify-content:space-between; padding:12px 20px; }
        .topbar .brand { font-weight:bold; font-size:20px; letter-spacing:1px; }
        .topbar .right a { color:#fff; text-decoration:none; margin-left:16px; }
        .layout { display:flex; min-height:calc(100vh - 50px); }
        .sidebar { width:230px; background:#1c2333; color:#cfd3dc; padding:14px 0; }
        .sidebar a { display:block; padding:10px 20px; color:#cfd3dc; text-decoration:none; font-size:14px; }
        .sidebar a:hover, .sidebar a.active { background:#2a3245; color:#fff; }
        .content { flex:1; padding:24px; }
        .card-row { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
        .card { flex:1; min-width:200px; border-radius:6px; padding:16px; color:#fff; }
        .card small { opacity:.85; }
        table { width:100%; border-collapse: collapse; background:#fff; }
        th { background:#e07b1e; color:#fff; padding:10px; text-align:left; }
        td { padding:10px; border-bottom:1px solid #eee; }
        .btn { display:inline-block; padding:8px 14px; background:#2980b9; color:#fff; border:none; border-radius:4px; text-decoration:none; cursor:pointer; }
        .btn.green { background:#27ae60; }
        .form-group { margin-bottom:14px; }
        label { display:block; margin-bottom:4px; font-weight:bold; font-size:13px; }
        input, select, textarea { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
        .flash-success { background:#d4edda; color:#155724; padding:10px; border-radius:4px; margin-bottom:16px; }
    </style>
</head>
<body>
<div class="topbar">
    <div class="brand">NASHAAD</div>
    <div class="right">
        <a href="<?= site_url('pos') ?>">POS</a>
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        <a href="<?= site_url('logout') ?>">Logout</a>
    </div>
</div>
<div class="layout">
    <div class="sidebar">
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>
        <a href="<?= site_url('items/list') ?>">Items/Products</a>
        <a href="<?= site_url('category/view') ?>">Categories</a>
        <a href="<?= site_url('units') ?>">Units</a>
        <a href="<?= site_url('brands') ?>">Brands</a>
        <a href="<?= site_url('stock/manager') ?>">Stock Manager</a>
        <a href="<?= site_url('stock/alerts') ?>">Stock Alert</a>
        <a href="<?= site_url('items/print-labels') ?>">Print Labels</a>
        <a href="<?= site_url('suppliers') ?>">Suppliers</a>
        <a href="<?= site_url('purchase/list') ?>">Purchase</a>
        <a href="<?= site_url('pos') ?>">POS / Sales</a>
    </div>
    <div class="content">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
