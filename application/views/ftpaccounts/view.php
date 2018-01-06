<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <? if ($this->permissions['ftpaccounts-edit']) { ?>
        <a class="btn" href="<?=$lang?>/ftpaccounts/edit"><?=$this->lang->line('add')?></a>
    <? } ?>
    <? if ($this->permissions['ftpaccounts-delete']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.ftpaccounts, 'id[]')) change_action(document.ftpaccounts,'<?=$lang?>/ftpaccounts/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <? } ?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="ftpaccounts" action="<?=$lang?>/ftpaccounts/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.ftpaccounts)">
            </th>
            <th>Host</th>
            <th>Port</th>
            <th>Username</th>
            <th>Password</th>
            <th>Order</th>
            <th>Action</th>
        </tr>

        <?if($ftpaccounts):?>

        <?foreach($ftpaccounts as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$ftp_host?></td>
                <td><?=$ftp_port?></td>
                <td><?=$item['userid']?></td>
                <td><?=$item['passwd']?></td>
                <td>
                    <?php echo $item['order_id'] ? 'Order ' . $item['order_id'] : ''; ?>
                </td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['ftpaccounts-edit'], 'url' => $lang.'/ftpaccounts/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['ftpaccounts-delete'], 'url' => $lang.'/ftpaccounts/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="4" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>