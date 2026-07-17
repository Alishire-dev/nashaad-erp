<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? esc($title) . ' - NASHAAD ERP' : 'NASHAAD ERP' ?></title>
    <style>
        * { box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin:0; background:#f0f2f5; color:#2c3038;
        }
        .topbar {
            background: linear-gradient(135deg, #e88a2e, #d96f0f);
            color:#fff; display:flex; align-items:center; justify-content:space-between;
            padding:14px 24px; box-shadow:0 2px 8px rgba(0,0,0,.12); position:sticky; top:0; z-index:50;
        }
        .topbar .brand { font-weight:700; font-size:21px; letter-spacing:1.5px; }
        .topbar .right a {
            color:#fff; text-decoration:none; margin-left:20px; font-size:14px;
            opacity:.92; transition: opacity .15s ease;
        }
        .topbar .right a:hover { opacity:1; text-decoration:underline; }
        .layout { display:flex; min-height:calc(100vh - 56px); }

        .sidebar {
            width:250px; background:#1a2036; color:#c9cedb; padding:12px 0; overflow-y:auto;
            box-shadow: 2px 0 8px rgba(0,0,0,.08); transition: margin-left .2s ease, width .2s ease;
        }
        .sidebar.collapsed { margin-left:-250px; }
        @media (max-width: 900px) {
            .sidebar { position:fixed; height:100vh; z-index:40; margin-left:-250px; }
            .sidebar.collapsed { margin-left:0; }
            .content { padding:20px 16px; }
            .topbar .brand { font-size:17px; }
            .branch-select { display:none; }
        }
        .sidebar a {
            display:block; padding:10px 20px; color:#c9cedb; text-decoration:none; font-size:14px;
            border-left:3px solid transparent; transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .sidebar a:hover, .sidebar a.active { background:#242b47; color:#fff; border-left-color:#e88a2e; }

        .nav-group > .nav-toggle {
            display:flex; justify-content:space-between; align-items:center;
            padding:10px 20px; cursor:pointer; font-size:14px; user-select:none;
            transition: background .15s ease;
        }
        .nav-group > .nav-toggle:hover { background:#242b47; }
        .nav-group .chevron { transition: transform .15s ease; font-size:11px; opacity:.7; }
        .nav-group.open .chevron { transform: rotate(90deg); }
        .nav-children { display:none; background:#141928; }
        .nav-group.open .nav-children { display:block; }
        .nav-children a { padding-left:38px; font-size:13px; }

        .nav-pending { opacity:.4; cursor:not-allowed; }
        .nav-pending:hover { background:none !important; border-left-color:transparent !important; }
        .soon-badge { font-size:10px; background:#3a4160; color:#c9cedb; padding:2px 7px; border-radius:10px; margin-left:6px; }

        .content { flex:1; padding:28px 32px; }
        .content h2 {
            font-size:22px; font-weight:600; margin:0 0 18px 0; padding-bottom:12px;
            border-bottom:2px solid #e88a2e22; color:#1a2036;
        }
        .content h3 { font-size:16px; font-weight:600; color:#3a4160; }

        .card-row { display:flex; gap:18px; flex-wrap:wrap; margin-bottom:22px; }
        .card {
            flex:1; min-width:200px; border-radius:10px; padding:18px; color:#fff;
            box-shadow:0 4px 12px rgba(0,0,0,.1); transition: transform .15s ease;
        }
        .card:hover { transform: translateY(-2px); }
        .card small { opacity:.85; }

        table {
            width:100%; border-collapse: collapse; background:#fff;
            border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06);
        }
        th {
            background:#e88a2e; color:#fff; padding:12px 10px; text-align:left;
            font-size:13px; font-weight:600; letter-spacing:.3px;
        }
        td { padding:11px 10px; border-bottom:1px solid #f0f1f4; font-size:14px; }
        tr:hover td { background:#fafbfc; }

        .btn {
            display:inline-block; padding:9px 16px; background:#3a8fd6; color:#fff;
            border:none; border-radius:6px; text-decoration:none; cursor:pointer;
            font-size:13px; font-weight:500; box-shadow:0 1px 3px rgba(0,0,0,.12);
            transition: background .15s ease, transform .1s ease;
        }
        .btn:hover { background:#2f7ac0; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn.green { background:#2fae63; }
        .btn.green:hover { background:#259050; }

        .form-group { margin-bottom:16px; }
        label { display:block; margin-bottom:5px; font-weight:600; font-size:13px; color:#3a4160; }
        input, select, textarea {
            width:100%; padding:9px 10px; border:1px solid #dde1e8; border-radius:6px;
            font-size:14px; transition: border-color .15s ease, box-shadow .15s ease;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline:none; border-color:#e88a2e; box-shadow:0 0 0 3px #e88a2e22;
        }

        .topbar .left { display:flex; align-items:center; gap:16px; }
        .hamburger { cursor:pointer; font-size:20px; opacity:.9; }
        .links-dropdown { position:relative; }
        .links-btn {
            background:#2c2f38; color:#fff; border:none; padding:8px 14px; border-radius:6px;
            cursor:pointer; font-size:13px; display:flex; align-items:center; gap:6px;
        }
        .links-menu {
            display:none; position:absolute; left:0; top:110%; background:#fff; color:#2c3038;
            border-radius:6px; box-shadow:0 6px 20px rgba(0,0,0,.18); min-width:160px; z-index:60;
        }
        .links-menu.open { display:block; }
        .links-menu a { display:block; padding:10px 16px; color:#2c3038; text-decoration:none; font-size:14px; }
        .links-menu a:hover { background:#f4f5f7; }
        .branch-select {
            background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.3);
            padding:7px 12px; border-radius:6px; font-size:13px;
        }
        .branch-select option { color:#2c3038; }
        .topbar .right { display:flex; align-items:center; gap:10px; }
        .topbar .right a.icon-btn {
            background:rgba(255,255,255,.15); padding:8px 14px; border-radius:6px;
            margin-left:0; display:flex; align-items:center; gap:6px; opacity:1;
        }
        .topbar .right a.icon-btn:hover { background:rgba(255,255,255,.28); text-decoration:none; }
        .topbar .right a.logout-btn {
            background:#c0392b; padding:8px 14px; border-radius:6px; margin-left:0;
        }
        .topbar .right a.logout-btn:hover { background:#a93226; text-decoration:none; }

        .user-block { text-align:center; padding:20px 16px; border-bottom:1px solid #2a3150; margin-bottom:8px; }
        .user-avatar {
            width:64px; height:64px; border-radius:50%; background:#e88a2e; color:#fff;
            display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:600;
            margin:0 auto 10px auto;
        }
        .user-name { color:#fff; font-size:14px; font-weight:600; }

        .quick-links-btn {
            background:#b8956a; color:#fff; border:none; padding:9px 16px; border-radius:6px;
            cursor:pointer; font-size:13px; font-weight:500; display:inline-flex; align-items:center; gap:6px;
        }
        .quick-links-menu {
            display:none; position:absolute; left:0; top:110%; background:#fff; border-radius:6px;
            box-shadow:0 6px 20px rgba(0,0,0,.18); padding:6px 0; min-width:220px; z-index:60;
        }
        .quick-links-menu.open { display:block; }
        .quick-links-menu a { display:block; padding:9px 16px; color:#2c3038; text-decoration:none; font-size:14px; }
        .quick-links-menu a:hover { background:#f4f5f7; }

        .flash-success {
            background:#e3f7ea; color:#1c7c43; padding:12px 16px; border-radius:8px;
            margin-bottom:18px; border-left:4px solid #2fae63; font-size:14px;
        }
    </style>
</head>
<body>
<div class="topbar">
    <div class="left">
        <span class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('collapsed')">&#9776;</span>
        <div class="links-dropdown">
            <button class="links-btn" onclick="toggleLinksMenu()">+ Links</button>
            <div class="links-menu" id="linksMenu">
                <a href="<?= site_url('purchase/add') ?>">+ Purchase</a>
                <a href="<?= site_url('pos') ?>">+ Sales</a>
                <a href="<?= site_url('customers/add') ?>">+ Customer</a>
                <a href="<?= site_url('suppliers/add') ?>">+ Supplier</a>
                <a href="<?= site_url('items/add') ?>">+ Item</a>
                <a href="#" class="nav-pending" onclick="return false;">+ Expense</a>
            </div>
        </div>
        <div class="brand">NASHAAD</div>
    </div>
    <div class="right">
        <select class="branch-select" disabled>
            <option>Main Branch</option>
        </select>
        <a class="icon-btn" href="<?= site_url('pos') ?>">🛒 POS</a>
        <a class="icon-btn" href="<?= site_url('dashboard') ?>">📊 Dashboard</a>
        <a class="logout-btn" href="<?= site_url('logout') ?>">⏻ Logout</a>
    </div>
</div>
<div class="layout">
    <div class="sidebar" id="sidebar">
        <?php $sessionUser = session()->get('user') ?? []; ?>
        <div class="user-block">
            <div class="user-avatar"><?= esc(strtoupper(substr($sessionUser['full_name'] ?? 'U', 0, 1))) ?></div>
            <div class="user-name"><?= esc($sessionUser['full_name'] ?? 'User') ?></div>
        </div>
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
                <a href="<?= site_url('issued-products') ?>">Issued Products</a>
                <a href="<?= site_url('damaged-products') ?>">Damaged Products</a>
                <a href="<?= site_url('price-change-log') ?>">Price Change Log</a>
                <a href="<?= site_url('stock-conversion') ?>">Stock Conversion</a>
                <a href="<?= site_url('stock/alerts') ?>">Stock Alert</a>
            </div>
        </div>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Purchase <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('purchase/add') ?>">New Purchase</a>
                <a href="<?= site_url('purchase/list') ?>">Purchase List</a>
                <a href="<?= site_url('purchase/returns') ?>">Purchase Return</a>
                <a href="<?= site_url('purchase/debit-notes') ?>">Debit Notes</a>
            </div>
        </div>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Sales <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('pos') ?>">POS</a>
                <a href="<?= site_url('sales/list') ?>">Sales List</a>
                <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Sales Return <span class="soon-badge">soon</span></a>
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
                <a href="<?= site_url('customers') ?>">Customers List</a>
            </div>
        </div>

        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Expenses <span class="soon-badge">soon</span></a>

        <div class="nav-group">
            <div class="nav-toggle" onclick="toggleGroup(this)">Accounting <span class="chevron">&#9656;</span></div>
            <div class="nav-children">
                <a href="<?= site_url('accounting/account-types') ?>">Accounts Type</a>
                <a href="<?= site_url('accounting/sub-account-types') ?>">Sub-Accounts Type</a>
                <a href="<?= site_url('accounting/chart-of-accounts') ?>">Chart of Accounts</a>
                <a href="<?= site_url('accounting/money') ?>">Money</a>
                <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Journal Entry <span class="soon-badge">soon</span></a>
            </div>
        </div>

        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Documents/Files <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Deferred — see README" onclick="return false;">Manufacturing <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Coming Day 6" onclick="return false;">Reports Manager <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Users Management <span class="soon-badge">soon</span></a>
        <a class="nav-pending" href="#" title="Not yet scheduled" onclick="return false;">Settings <span class="soon-badge">soon</span></a>
    </div>
    <div class="content">
        <?php if (isset($quickLinksView)): ?>
            <div style="margin-bottom:16px;">
                <?= view($quickLinksView) ?>
            </div>
        <?php endif; ?>
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
function toggleLinksMenu() {
    document.getElementById('linksMenu').classList.toggle('open');
}
function toggleQuickLinks() {
    document.getElementById('quickLinksMenu').classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.links-dropdown')) {
        document.querySelectorAll('.links-menu.open, .quick-links-menu.open').forEach(m => m.classList.remove('open'));
    }
});
</script>
