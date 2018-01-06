<ul class="cliplog-submissions-list">
<?php if($submissions) { ?>
    <?php foreach($submissions as $year_key => $year) { ?>
        <li<?php echo $year['selected'] ? '' : ' class="collapsed"';?>>
            <?php echo $year_key; ?>
            <ul>
                <?php foreach($year['months'] as $month_key => $month) { ?>
                    <li<?php echo $month['selected'] ? '' : ' class="collapsed"';?>>
                        <?php echo $month_key; ?>
                        <ul>
                            <?php foreach($month['submissions'] as $submission_item) { ?>
                            <li<?php if($submission_item['id'] == $_REQUEST['submission']) echo ' class="selected"'; ?> id="cliplog-submissions-<?php echo $submission_item['id']; ?>"><a href="<?php echo $lang; ?>/cliplog/view/?submission=<?php echo $submission_item['id'] . (isset($_REQUEST['active']) ? '&active=' . $_REQUEST['active'] : ''); ?>"><?php echo $submission_item['code']; ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
<?php } ?>
</ul>
