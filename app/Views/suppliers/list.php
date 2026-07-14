<h2>Suppliers List</h2>
<a class="btn green" href="<?= site_url('suppliers/add') ?>">+ Add Supplier</a>
<br><br>
<table>
    <tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Status</th></tr>
    <?php foreach ($suppliers as $s): ?>
    <tr>
        <td><?= $s['id'] ?></td>
        <td><?= esc($s['name']) ?></td>
        <td><?= esc($s['phone'] ?? '-') ?></td>
        <td><?= esc($s['email'] ?? '-') ?></td>
        <td><?= ucfirst($s['status']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($suppliers)): ?>
    <tr><td colspan="5">No suppliers yet.</td></tr>
    <?php endif; ?>
</table>
