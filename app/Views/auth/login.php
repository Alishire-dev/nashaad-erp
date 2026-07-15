<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
               background:#f0f2f5; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .box { background:#fff; padding:40px; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.1); width:320px; }
        h2 { text-align:center; color:#1a2036; margin-bottom:24px; font-weight:700; letter-spacing:1px; }
        input { width:100%; padding:11px; margin-bottom:14px; border:1px solid #dde1e8; border-radius:6px; box-sizing:border-box;
                font-size:14px; transition: border-color .15s ease, box-shadow .15s ease; }
        input:focus { outline:none; border-color:#e88a2e; box-shadow:0 0 0 3px #e88a2e22; }
        button { width:100%; padding:11px; background: linear-gradient(135deg, #e88a2e, #d96f0f); color:#fff; border:none;
                 border-radius:6px; cursor:pointer; font-weight:600; font-size:14px; box-shadow:0 2px 8px rgba(217,111,15,.3);
                 transition: transform .1s ease; }
        button:hover { transform: translateY(-1px); }
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
