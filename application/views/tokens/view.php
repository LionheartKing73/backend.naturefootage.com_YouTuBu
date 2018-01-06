<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <? if ($this->permissions['tokens-edit']) { ?>
        <a class="btn" href="<?=$lang?>/tokens/edit"><?=$this->lang->line('add')?></a>
    <? } ?>
    <? if ($this->permissions['tokens-visible']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.tokens, 'id[]')) change_action(document.tokens,'<?= $lang ?>/tokens/visible');">
            <?= $this->lang->line('visible'); ?>
        </a>
    <? } ?>
    <? if ($this->permissions['tokens-delete']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.tokens, 'id[]')) change_action(document.tokens,'<?=$lang?>/tokens/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <? } ?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="tokens" action="<?=$lang?>/tokens/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.tokens)">
            </th>
            <th>Token</th>
            <th>Path</th>
            <th>Order</th>
            <th>Status</th>
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
                <td><? if ($item['status'] == 1) echo 'Active'; else echo 'Inactive'; ?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['tokens-edit'], 'url' => $lang.'/tokens/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['tokens-delete'], 'url' => $lang.'/tokens/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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