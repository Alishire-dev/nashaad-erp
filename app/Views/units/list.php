<h2>Units List</h2>
<a class="btn green" href="<?= site_url('units/add') ?>">+ New Unit</a>
<br><br>
<table>
    <tr><th>#</th><th>Name</th><th>Short Name</th><th>Status</th></tr>
    <?php foreach ($units as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= esc($u['name']) ?></td>
        <td><?= esc($u['short_name']) ?></td>
        <td><?= ucfirst($u['status']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
