<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['bitdepths-edit']) : ?>
    <a class="btn" href="<?=$lang?>/bitdepths/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['bitdepths-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.bitdepths,'<?=$lang?>/bitdepths/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['bitdepths-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.bitdepths, 'id[]')) change_action(document.bitdepths,'<?=$lang?>/bitdepths/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="bitdepths" action="<?=$lang?>/bitdepths/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.bitdepths)">
            </th>
            <th>Bit depth</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($bit_depths):?>

        <?foreach($bit_depths as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['name']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['bitdepths-edit'], 'url' => $lang.'/bitdepths/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['bitdepths-delete'], 'url' => $lang.'/bitdepths/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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