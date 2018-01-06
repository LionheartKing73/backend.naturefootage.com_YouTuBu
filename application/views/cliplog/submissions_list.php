<ul class="cliplog-submissions-list">
<?php if($submissions) { ?>
    <?php foreach($submissions as $submission_item) { ?>
        <li<?php if($submission_item['id'] == $_REQUEST['submission']) echo ' class="selected"'; ?> id="cliplog-submissions-<?php echo $submission_item['id']; ?>"><a href="<?php echo $lang; ?>/cliplog/view/?submission=<?php echo $submission_item['id'] . (isset($_REQUEST['active']) ? '&active=' . $_REQUEST['active'] : ''); ?>"><?php echo $submission_item['code']; ?></a></li>
    <?php } ?>
<?php } ?>
</ul>