<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['discount_display_rf-edit']) : ?>
    <a class="btn" href="<?=$lang?>/discount_display_rf/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['discount_display_rf-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.discount_display_rf, 'id[]')) change_action(document.discount_display_rf,'<?=$lang?>/discount_display_rf/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="discount_display_rf" action="<?=$lang?>/discount_display_rf/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.discount_display_rf)">
            </th>
            <th>Type</th>
            <th>Action</th>
        </tr>

        <?if($discount_displays):?>

        <?foreach($discount_displays as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['type']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['discount_display_rf-edit'], 'url' => $lang.'/discount_display_rf/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['discount_display_rf-delete'], 'url' => $lang.'/discount_display_rf/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
            <?endforeach?>
        </td></tr>
        <?else:?>
        <tr><td colspan="3" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
<div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>