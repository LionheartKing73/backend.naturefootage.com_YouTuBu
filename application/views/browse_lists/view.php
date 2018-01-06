<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <? if ($this->permissions['browselists-edit']) { ?>
        <a class="btn" href="<?=$lang?>/browselists/edit"><?=$this->lang->line('add')?></a>
    <? } ?>
    <? if ($this->permissions['browselists-ord']) { ?>
        <a class="btn" href="javascript: change_action(document.browselists,'<?=$lang?>/browselists/ord')">
            <?=$this->lang->line('save_ord')?></a>
    <? } ?>
    <? if ($this->permissions['browselists-visible']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.browselists, 'id[]')) change_action(document.browselists,'<?= $lang ?>/browselists/visible');">
            <?= $this->lang->line('visible'); ?>
        </a>
    <? } ?>
    <? if ($this->permissions['browselists-delete']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.browselists, 'id[]')) change_action(document.browselists,'<?=$lang?>/browselists/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <? } ?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="browselists" action="<?=$lang?>/browselists/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.browselists)">
            </th>
            <th>Title</th>
            <th>Sort</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?if($browse_lists):?>

        <?foreach($browse_lists as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['title']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td><? if ($item['status'] == 1) echo 'Active'; else echo 'Inactive'; ?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['browselists-edit'], 'url' => $lang.'/browselists/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['browselists-items'], 'url' => $lang.'/browselists/items/'.$item['id'], 'name' => 'Items'),
                        array('display' => $this->permissions['browselists-delete'], 'url' => $lang.'/browselists/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
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