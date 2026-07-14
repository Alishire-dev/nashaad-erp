<h2>Categories List</h2>
<a class="btn green" href="<?= site_url('category/add') ?>">+ Add Category</a>
<br><br>
<table>
    <tr><th>#</th><th>Code</th><th>Name</th><th>Status</th></tr>
    <?php foreach ($categories as $c): ?>
    <tr>
        <td><?= $c['id'] ?></td>
        <td><?= esc($c['category_code']) ?></td>
        <td><?= esc($c['name']) ?></td>
        <td><?= ucfirst($c['status']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
