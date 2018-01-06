<ul class="cliplog-galleries-list">
<?php if($galleries) { ?>
    <?php foreach($galleries as $gallery_item) { ?>
        <li<?php if($gallery_item['id'] == $_REQUEST['gallery']) echo ' class="selected"'; ?> id="cliplog-galleries-<?php echo $gallery_item['id']; ?>"><a href="<?php echo $lang; ?>/cliplog/view/?gallery=<?php echo $gallery_item['id'] . (isset($_REQUEST['active']) ? '&active=' . $_REQUEST['active'] : ''); ?>"><?php echo $gallery_item['title']; ?></a></li>
    <?php } ?>
<?php } ?>
</ul>