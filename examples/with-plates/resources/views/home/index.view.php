<?php $this->layout('layout/master', ['title' => 'Index']) ?>

<h1>Home Page!</h1>

<h2>Users</h2>
<?php if (isset($users) and count($users) > 0) : ?>
<ul>
<?php foreach ($users as $u) : ?>
    <li><?= $this->e($u) ?></li>
<?php endforeach ?>
</ul>
<?php else : ?>
<p>No users</p>
<?php endif ?>