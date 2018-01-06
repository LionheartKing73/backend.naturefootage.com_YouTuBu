<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['deliveryoptions-edit']) : ?>
        <a class="btn" href="<?=$lang?>/deliveryoptions/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['deliveryoptions-ord']) : ?>
        <a class="btn" href="javascript: change_action(document.deliveryoptions,'<?=$lang?>/deliveryoptions/ord')">
            <?=$this->lang->line('save_ord')?>
        </a>
    <?endif?>
    <?if ($this->permissions['deliveryoptions-delete']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.deliveryoptions, 'id[]')) change_action(document.deliveryoptions,'<?=$lang?>/deliveryoptions/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="deliveryoptions" action="<?=$lang?>/deliveryoptions/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.deliveryoptions)">
            </th>
            <th>Categories</th>
            <th>Source</th>
            <th>Destination</th>
            <th>Description</th>
            <th>Price</th>
            <th>Delivery</th>
            <th>Display order</th>
            <th>Action</th>
        </tr>

        <?if($deliveryoptions):?>

        <?foreach($deliveryoptions as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['categories']?></td>
                <td><?=$item['source']?></td>
                <td><?=$item['destination']?></td>
                <td><?=$item['description']?></td>
                <td><?=$item['price']?></td>
                <td><?=$item['delivery']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['display_order']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['deliveryoptions-edit'], 'url' => $lang.'/deliveryoptions/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['deliveryoptions-delete'], 'url' => $lang.'/deliveryoptions/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="9" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>