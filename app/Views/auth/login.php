<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f5f7; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .box { background:#fff; padding:40px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.1); width:320px; }
        h2 { text-align:center; color:#e07b1e; margin-bottom:20px; }
        input { width:100%; padding:10px; margin-bottom:14px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; }
        button { width:100%; padding:10px; background:#e07b1e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-weight:bold; }
        .error { color:#c0392b; margin-bottom:10px; font-size:14px; }
    </style>
</head>
<body>
    <form class="box" method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <h2>NASHAAD ERP</h2>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
