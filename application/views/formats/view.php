<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['formats-edit']) : ?>
    <a class="btn" href="<?=$lang?>/formats/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['formats-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.formats,'<?=$lang?>/formats/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['formats-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.formats, 'id[]')) change_action(document.formats,'<?=$lang?>/formats/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="formats" action="<?=$lang?>/formats/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.formats)">
            </th>
            <th>Format</th>
            <!--<th>Price level</th>-->
            <th>Master</th>
            <th>Source</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($formats):?>

        <?foreach($formats as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['format']?></td>
                <!--<td><?=$item['price_level']?></td>-->
                <td><?=$item['master']?></td>
                <td><?=$item['camera']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['formats-edit'], 'url' => $lang.'/formats/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['formats-delete'], 'url' => $lang.'/formats/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="7" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>