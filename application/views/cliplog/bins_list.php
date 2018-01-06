<ul class="cliplog-bins-list">
<?php if($bins) { ?>
    <?php foreach($bins as $bin_item) { ?>
        <li<?php if($bin_item['id'] == $_REQUEST['bin']) echo ' class="selected"'; ?> id="cliplog-bins-<?php echo $bin_item['id']; ?>"><a href="<?php echo $lang; ?>/cliplog/view/?bin=<?php echo $bin_item['id']; ?>"><?php echo $bin_item['title']; ?></a></li>
    <?php } ?>
<?php } ?>
</ul>