<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <? if ($this->permissions['upload_tokens-edit']) { ?>
        <a class="btn" href="<?=$lang?>/upload_tokens/edit"><?=$this->lang->line('add')?></a>
    <? } ?>
    <? if ($this->permissions['upload_tokens-visible']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.upload_tokens, 'id[]')) change_action(document.upload_tokens,'<?= $lang ?>/upload_tokens/visible');">
            <?= $this->lang->line('visible'); ?>
        </a>
    <? } ?>
    <? if ($this->permissions['upload_tokens-delete']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.upload_tokens, 'id[]')) change_action(document.upload_tokens,'<?=$lang?>/upload_tokens/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <? } ?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="upload_tokens" action="<?=$lang?>/upload_tokens/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.tokens)">
            </th>
            <th>Token</th>
            <th>Path</th>
            <th>Order</th>
            <th>Status</th>
            <th>Lab Name</th>
            <th>Action</th>
        </tr>

        <?if($tokens):?>

        <?foreach($tokens as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['token']?></td>
                <td><?=$item['path']?></td>
                <td><?php echo $item['order_id'] ? 'Order ' . $item['order_id'] : ''; ?></td>
                <td><? if ($item['is_active'] == 1) echo 'Active'; else echo 'Inactive'; ?></td>
                <td><?=$item['lab_name']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['upload_tokens-edit'], 'url' => $lang.'/upload_tokens/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['upload_tokens-delete'], 'url' => $lang.'/upload_tokens/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="6" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>