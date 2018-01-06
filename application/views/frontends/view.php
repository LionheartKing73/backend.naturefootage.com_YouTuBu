<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<?php if($frontends) {
    foreach($frontends as $item) {
        $url = parse_url('http://'. str_replace('http://', '', $item['host_name']), PHP_URL_HOST);
        $form_id = str_replace('.', '_', $url); ?>
        <form action="http://<?php echo $url; ?>/wp-login.php" method="post" id="<?php echo $form_id; ?>" target="_blank">
            <input type="hidden" name="log" value="<?php echo $item['login']; ?>">
            <input type="hidden" name="pwd" value="<?php echo $item['password']; ?>">
            <input type="hidden" name="redirect_to" value="http://<?php echo $url; ?>/wp-admin">
        </form>
<?php }} ?>

<form name="frontends" action="<?=$lang?>/frontends/view" method="post">
    <table class="table table-striped">
        <tr>
            <th>Title</th>
            <th>Host Name</th>
            <th>Provider</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?if($frontends):?>

        <?foreach($frontends as $item):?>
            <tr>
                <td><?=$item['name']?></td>
                <td><?=$item['host_name']?></td>
                <td><?=$item['fname'] . ' ' . $item['lname']; ?></td>
                <td><? if ($item['status'] == 1) echo 'Active'; else echo 'Inactive'; ?></td>
                <td>
                    <?php
                        $url = parse_url('http://'. str_replace('http://', '', $item['host_name']), PHP_URL_HOST);
                        $form_id = str_replace('.', '_', $url);
                    ?>
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            Action <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="http://<?php echo $url; ?>/wp-admin" onclick="document.getElementById('<?php echo $form_id; ?>').submit(); return false;">Manage</a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="5" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>