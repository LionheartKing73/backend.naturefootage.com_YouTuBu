<p>User <?php echo $user['fname'] . ' ' . $user['lname']; ?> uploaded files <?php echo date('d.m.Y H:i'); ?>:</p>
<ul>
<?php foreach($paths as $path) { ?>
    <li><?php echo $path; ?></li>
<?php } ?>
</ul>