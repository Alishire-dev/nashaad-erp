<h2>Brands List</h2>
<a class="btn green" href="<?= site_url('brands/add') ?>">+ New Brand</a>
<br><br>
<table>
    <tr><th>#</th><th>Name</th><th>Status</th></tr>
    <?php foreach ($brands as $b): ?>
    <tr>
        <td><?= $b['id'] ?></td>
        <td><?= esc($b['name']) ?></td>
        <td><?= ucfirst($b['status']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($brands)): ?>
    <tr><td colspan="3">No brands yet.</td></tr>
    <?php endif; ?>
</table>
