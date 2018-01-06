<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['digitalfileformats-edit']) : ?>
    <a class="btn" href="<?=$lang?>/digitalfileformats/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['digitalfileformats-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.digitalfileformats,'<?=$lang?>/digitalfileformats/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['digitalfileformats-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.digitalfileformats, 'id[]')) change_action(document.digitalfileformats,'<?=$lang?>/digitalfileformats/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="digitalfileformats" action="<?=$lang?>/digitalfileformats/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.digitalfileformats)">
            </th>
            <th>Format</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($digital_file_formats):?>

        <?foreach($digital_file_formats as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['format']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['digitalfileformats-edit'], 'url' => $lang.'/digitalfileformats/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['digitalfileformats-delete'], 'url' => $lang.'/digitalfileformats/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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