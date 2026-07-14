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

        .sidebar { width:250px; background:#1c2333; color:#cfd3dc; padding:10px 0; overflow-y:auto; }
        .sidebar a { display:block; padding:10px 20px; color:#cfd3dc; text-decoration:none; font-size:14px; }
        .sidebar a:hover, .sidebar a.active { background:#2a3245; color:#fff; }

        .nav-group > .nav-toggle {
            display:flex; justify-content:space-between; align-items:center;
            padding:10px 20px; cursor:pointer; font-size:14px; user-select:none;
        }
        .nav-group > .nav-toggle:hover { background:#2a3245; }
        .nav-group .chevron { transition: transform .15s ease; font-size:11px; opacity:.7; }
        .nav-group.open .chevron { transform: rotate(90deg); }
        .nav-children { display:none; background:#161b28; }
        .nav-group.open .nav-children { display:block; }
        .nav-children a { padding-left:38px; font-size:13px; }

        .nav-pending { opacity:.45; cursor:not-allowed; }
        .nav-pending:hover { background:none !important; }
        .soon-badge { font-size:10px; background:#444; color:#ccc; padding:1px 6px; border-radius:8px; margin-left:6px; }

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
    <div class="sidebar" id="sidebar">
        <a href="<?= site_url('dashboard') ?>">Dashboard</a>

        <div class="nav-group open">
            <div class="nav-toggle" onclick="toggleGroup(this)">Items/Products <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('items/add') ?>">New Item</a>
                <a href="<?= site_url('items/list') ?>">Items List</a>
                <a href="<?= site_url('stock/manager') ?>">Stock Manager</a>
                <a href="<?= site_url('category/view') ?>">Categories List</a>
                <a href="<?= site_url('brands') ?>">Brands List</a>
                <a href="<?= site_url('units') ?>">Unit List (UOM)</a>
                <a href="<?= site_url('items/print-labels') ?>">Print Labels</a>
                <a class="nav-pending" href="#" title="Coming Day 6" onclick="return false;">Issued Products <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Coming Day 6" onclick="return false;">Damaged Products <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Price Change Log <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Stock Conversion <span class="soon-badge">soon</span></a>
                <a href="<?= site_url('stock/alerts') ?>">Stock Alert</a>
            </div>
        </div>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Purchase <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('purchase/add') ?>">New Purchase</a>
                <a href="<?= site_url('purchase/list') ?>">Purchase List</a>
                <a href="<?= site_url('purchase/returns') ?>">Purchase Return</a>
            </div>
        </div>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Sales <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('pos') ?>">POS</a>
                <a class="nav-pending" href="#" title="Coming Day 4" onclick="return false;">Sales List <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Coming Day 4" onclick="return false;">Sales Return <span class="soon-badge">soon</span></a>
            </div>
        </div>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Suppliers <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('suppliers') ?>">Suppliers List</a>
                <a href="<?= site_url('suppliers/add') ?>">Add Supplier</a>
            </div>
        </div>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Customers <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a class="nav-pending" href="#" title="Coming Day 4" onclick="return false;">Customers List <span class="soon-badge">soon</span></a>
            </div>
        </div>

        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Expenses <span class="soon-badge">soon</span></a>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Accounting <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a class="nav-pending" href="#" title="Coming Day 5" onclick="return false;">Accounts Type <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Coming Day 5" onclick="return false;">Chart of Accounts <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Coming Day 5" onclick="return false;">Money <span class="soon-badge">soon</span></a>
                <a class="nav-pending" href="#" title="Coming Day 5" onclick="return false;">Journal Entry <span class="soon-badge">soon</span></a>
            </div>
        </div>

        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Documents/Files <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Deferred — see README" onclick="return false;">Manufacturing <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Coming Day 6" onclick="return false;">Reports Manager <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Users Management <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Settings <span class="soon-badge">soon</span></a>
    </div>
    <div class="content">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="flash-success" style="background:#f8d7da; color:#721c24;"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

<script>
function toggleGroup(el) {
    el.parentElement.classList.toggle('open');
}
</script>
