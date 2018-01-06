<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['delivery_categories-edit']) : ?>
    <a class="btn" href="<?=$lang?>/delivery_categories/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['delivery_categories-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.delivery_categories,'<?=$lang?>/delivery_categories/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['delivery_categories-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.delivery_categories, 'id[]')) change_action(document.delivery_categories,'<?=$lang?>/delivery_categories/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="delivery_categories" action="<?=$lang?>/delivery_categories/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.delivery_categories)">
            </th>
            <th>Code</th>
            <th>Description</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($delivery_categories):?>

        <?foreach($delivery_categories as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['pk']?>">
                </td>
                <td><?=$item['id']?></td>
                <td><?=$item['description']?></td>
                <td><input type="text" name="ord[<?=$item['pk']?>]" style="width:30px" value="<?=$item['display_order']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['delivery_categories-edit'], 'url' => $lang.'/delivery_categories/edit/'.$item['pk'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['delivery_categories-delete'], 'url' => $lang.'/delivery_categories/delete/'.$item['pk'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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