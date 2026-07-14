<h2>Categories List</h2>
<a class="btn green" href="<?= site_url('category/add') ?>">+ Add Category</a>
<br><br>
<table>
    <tr><th>#</th><th>Code</th><th>Name</th><th>Parent</th><th>Show on POS</th><th>Status</th></tr>
    <?php foreach ($categories as $c): ?>
    <tr>
        <td><?= $c['id'] ?></td>
        <td><?= esc($c['category_code']) ?></td>
        <td><?= esc($c['name']) ?></td>
        <td><?= esc($c['parent_name'] ?? '-') ?></td>
        <td>
            <?php if ((int) ($c['show_on_pos'] ?? 1) === 1): ?>
                <span style="background:#27ae60; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">Yes</span>
            <?php else: ?>
                <span style="background:#c0392b; color:#fff; padding:3px 8px; border-radius:3px; font-size:12px;">No</span>
            <?php endif; ?>
        </td>
        <td><?= ucfirst($c['status']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($categories)): ?>
    <tr><td colspan="6">No categories yet.</td></tr>
    <?php endif; ?>
</table>
