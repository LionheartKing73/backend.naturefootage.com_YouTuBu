<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['colorspaces-edit']) : ?>
    <a class="btn" href="<?=$lang?>/colorspaces/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['colorspaces-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.colorspaces,'<?=$lang?>/colorspaces/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['colorspaces-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.colorspaces, 'id[]')) change_action(document.colorspaces,'<?=$lang?>/colorspaces/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="colorspaces" action="<?=$lang?>/colorspaces/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.colorspaces)">
            </th>
            <th>Bit depth</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($color_spaces):?>

        <?foreach($color_spaces as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['name']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['colorspaces-edit'], 'url' => $lang.'/colorspaces/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['colorspaces-delete'], 'url' => $lang.'/colorspaces/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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