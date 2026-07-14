<h2>Add Supplier</h2>
<form method="post" action="<?= site_url('suppliers/add') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Name*</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
    <div class="form-group"><label>Email</label><input type="email" name="email"></div>
    <div class="form-group"><label>Address</label><textarea name="address" rows="3"></textarea></div>
    <button class="btn green" type="submit">Save</button>
</form>
