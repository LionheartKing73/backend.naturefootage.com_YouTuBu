<ul class="cliplog-sequences-list">
<?php if($sequences) { ?>
    <?php foreach($sequences as $sequence_item) { ?>
        <li<?php if($sequence_item['id'] == $_REQUEST['sequence']) echo ' class="selected"'; ?> id="cliplog-sequences-<?php echo $sequence_item['id']; ?>"><a href="<?php echo $lang; ?>/cliplog/view/?sequence=<?php echo $sequence_item['id'] . (isset($_REQUEST['active']) ? '&active=' . $_REQUEST['active'] : ''); ?>"><?php echo $sequence_item['title']; ?></a></li>
    <?php } ?>
<?php } ?>
</ul>