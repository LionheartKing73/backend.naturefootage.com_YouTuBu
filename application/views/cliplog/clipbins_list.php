<ul class="cliplog-clipbins-list">
<?php if($clipbins) { ?>
    <?php foreach($clipbins as $clipbin_item) { ?>
        <li<?php if($clipbin_item['id'] == $_REQUEST['backend_clipbin']) echo ' class="selected"'; ?> id="cliplog-clipbins-<?php echo $clipbin_item['id']; ?>"><a href="<?php echo $lang; ?>/cliplog/view/?backend_clipbin=<?php echo $clipbin_item['id']; ?>"><?php echo $clipbin_item['title']; ?><?php if($is_admin) echo ' (' . $clipbin_item['fname'] . ' '. $clipbin_item['lname'] . ')'; ?></a></li>
    <?php } ?>
<?php } ?>
</ul>